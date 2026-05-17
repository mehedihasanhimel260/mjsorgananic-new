<?php

namespace App\Services;

use App\Jobs\SendWeeklySmsBatchJob;
use App\Models\SmsCampaign;
use App\Models\SmsLog;
use App\Models\SmsSetting;
use App\Models\SmsTemplate;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SmsCampaignService
{
    public const WEEKLY_BATCH_SIZE = 10;

    public const DEFAULT_SCHEDULE_DAY = 5;

    public const DEFAULT_SCHEDULE_TIME = '10:00';

    public const BATCH_DELAY_SECONDS = 15;

    public function __construct(private readonly SmsGatewayService $smsGatewayService) {}

    public function getScheduleSetting(): SmsSetting
    {
        return SmsSetting::query()->firstOrCreate([], [
            'transaction_type' => 'T',
            'schedule_enabled' => true,
            'schedule_day_of_week' => self::DEFAULT_SCHEDULE_DAY,
            'schedule_time' => self::DEFAULT_SCHEDULE_TIME,
            'schedule_start_date' => now('Asia/Dhaka')->toDateString(),
        ]);
    }

    public function shouldRunScheduledCampaign(?CarbonImmutable $now = null): bool
    {
        $setting = $this->getScheduleSetting();
        $now ??= CarbonImmutable::now('Asia/Dhaka')->seconds(0);

        if (! $setting->schedule_enabled) {
            return false;
        }

        if ($setting->schedule_start_date && $now->toDateString() < $setting->schedule_start_date->toDateString()) {
            return false;
        }

        return (int) ($setting->schedule_day_of_week ?? self::DEFAULT_SCHEDULE_DAY) === $now->dayOfWeek
            && ($setting->schedule_time ?? self::DEFAULT_SCHEDULE_TIME) === $now->format('H:i');
    }

    public function createWeeklyCampaign(): ?SmsCampaign
    {
        $template = SmsTemplate::query()->where('is_weekly_active', true)->latest('id')->first();

        if (! $template) {
            return null;
        }

        [$weekStart, $weekEnd, $weekKey] = $this->currentWeekWindow();

        return DB::transaction(function () use ($template, $weekStart, $weekEnd, $weekKey) {
            return SmsCampaign::query()->firstOrCreate(
                [
                    'campaign_type' => 'weekly',
                    'week_key' => $weekKey,
                ],
                [
                    'sms_template_id' => $template->id,
                    'title' => $template->title,
                    'message' => $template->message,
                    'week_starts_at' => $weekStart,
                    'week_ends_at' => $weekEnd,
                    'status' => 'queued',
                    'batch_size' => self::WEEKLY_BATCH_SIZE,
                ]
            );
        });
    }

    public function dispatchCampaignBatches(SmsCampaign $campaign): int
    {
        $campaign->refresh();

        if (SmsLog::query()->where('sms_campaign_id', $campaign->id)->where('send_type', 'weekly')->exists()) {
            $this->refreshCampaignCounters($campaign);

            return 0;
        }

        $recipients = $this->eligibleWeeklyRecipients();

        if ($recipients->isEmpty()) {
            $campaign->update([
                'status' => 'completed',
                'total_recipients' => 0,
                'pending_recipients' => 0,
                'processing_recipients' => 0,
                'sent_recipients' => 0,
                'failed_recipients' => 0,
                'started_at' => $campaign->started_at ?? now(),
                'completed_at' => now(),
            ]);

            return 0;
        }

        $campaign->update([
            'status' => 'processing',
            'batch_size' => self::WEEKLY_BATCH_SIZE,
            'total_recipients' => $recipients->count(),
            'pending_recipients' => $recipients->count(),
            'started_at' => $campaign->started_at ?? now(),
            'completed_at' => null,
        ]);

        foreach ($recipients->chunk(self::WEEKLY_BATCH_SIZE)->values() as $index => $chunk) {
            SendWeeklySmsBatchJob::dispatch($campaign->id, $chunk->values()->all())
                ->delay(now()->addSeconds($index * self::BATCH_DELAY_SECONDS));
        }

        return (int) ceil($recipients->count() / self::WEEKLY_BATCH_SIZE);
    }

    public function sendBatch(SmsCampaign $campaign, array $recipients): void
    {
        $phones = collect($recipients)->pluck('phone')->implode(',');
        $result = $this->smsGatewayService->sendBulkSms($phones, $campaign->message);
        $now = now();

        foreach ($recipients as $recipient) {
            SmsLog::query()->create([
                'sms_campaign_id' => $campaign->id,
                'user_id' => $recipient['user_id'],
                'phone' => $recipient['phone'],
                'message' => $campaign->message,
                'send_type' => 'weekly',
                'campaign_key' => $campaign->week_key,
                'gateway_transaction_id' => $result['transaction_id'],
                'status_code' => $result['code'],
                'status_text' => $result['status_text'],
                'delivery_status' => $result['success'] ? 'processing' : 'failed',
                'delivery_attempts' => 0,
                'delivery_finalized_at' => $result['success'] ? null : $now,
                'gateway_response' => $result['raw_response'],
                'sent_at' => $now,
            ]);
        }

        $this->refreshCampaignCounters($campaign);
    }

    public function pollDeliveryStatuses(): int
    {
        $logs = SmsLog::query()
            ->where('send_type', 'weekly')
            ->where('delivery_status', 'processing')
            ->whereNotNull('gateway_transaction_id')
            ->whereNull('delivery_finalized_at')
            ->orderBy('id')
            ->limit(200)
            ->get();

        if ($logs->isEmpty()) {
            return 0;
        }

        foreach ($logs as $log) {
            $result = $this->smsGatewayService->checkDeliveryStatus((string) $log->gateway_transaction_id, $log->phone);
            $now = now();
            $deliveryStatus = $result['delivery_status'] ?? 'processing';

            $payload = [
                'status_code' => $result['status_code'],
                'status_text' => $result['status_text'],
                'gateway_response' => $result['raw_response'],
                'delivery_status_checked_at' => $now,
                'delivery_attempts' => DB::raw('delivery_attempts + 1'),
                'updated_at' => $now,
            ];

            if (in_array($deliveryStatus, ['delivered', 'failed'], true)) {
                $payload['delivery_status'] = $deliveryStatus;
                $payload['delivery_finalized_at'] = $now;
            }

            SmsLog::query()->whereKey($log->id)->update($payload);

            if ($log->sms_campaign_id && $campaign = SmsCampaign::query()->find($log->sms_campaign_id)) {
                $this->refreshCampaignCounters($campaign);
            }
        }

        return $logs->count();
    }

    public function refreshCampaignCounters(SmsCampaign $campaign): void
    {
        $base = SmsLog::query()
            ->where('sms_campaign_id', $campaign->id)
            ->where('send_type', 'weekly');

        $total = (clone $base)->count();
        $processing = (clone $base)->where('delivery_status', 'processing')->count();
        $sent = (clone $base)->where('delivery_status', 'delivered')->count();
        $failed = (clone $base)->where('delivery_status', 'failed')->count();
        $pending = max($total - ($processing + $sent + $failed), 0);

        $status = 'queued';
        $completedAt = null;

        if ($total === 0) {
            $status = 'completed';
            $completedAt = now();
        } elseif ($processing > 0 || $pending > 0) {
            $status = 'processing';
        } elseif ($failed > 0) {
            $status = $sent > 0 ? 'partially_failed' : 'failed';
            $completedAt = now();
        } else {
            $status = 'completed';
            $completedAt = now();
        }

        $campaign->update([
            'batch_size' => self::WEEKLY_BATCH_SIZE,
            'total_recipients' => $total,
            'pending_recipients' => $pending,
            'processing_recipients' => $processing,
            'sent_recipients' => $sent,
            'failed_recipients' => $failed,
            'status' => $status,
            'completed_at' => $completedAt,
            'started_at' => $campaign->started_at ?? now(),
        ]);
    }

    public function currentWeekKey(): string
    {
        [, , $weekKey] = $this->currentWeekWindow();

        return $weekKey;
    }

    private function eligibleWeeklyRecipients(): Collection
    {
        $cutoff = now()->subDays(7);
        $recentPhones = array_flip(
            SmsLog::query()
                ->where('send_type', 'weekly')
                ->where('created_at', '>=', $cutoff)
                ->pluck('phone')
                ->all()
        );

        $users = User::query()
            ->select('id', 'phone')
            ->where('status', 'verified')
            ->whereNotNull('phone')
            ->orderBy('id')
            ->get();

        $rows = [];
        $seenPhones = [];

        foreach ($users as $user) {
            $normalizedPhone = $this->smsGatewayService->normalizePhone($user->phone);

            if (! $normalizedPhone || isset($seenPhones[$normalizedPhone]) || isset($recentPhones[$normalizedPhone])) {
                continue;
            }

            $seenPhones[$normalizedPhone] = true;
            $rows[] = [
                'user_id' => $user->id,
                'phone' => $normalizedPhone,
            ];
        }

        return collect($rows);
    }

    private function currentWeekWindow(): array
    {
        $now = CarbonImmutable::now('Asia/Dhaka');
        $weekStart = $now->startOfWeek()->setTime(0, 0);
        $weekEnd = $weekStart->endOfWeek()->setTime(23, 59, 59);
        $weekKey = $weekStart->format('o-\\WW');

        return [$weekStart, $weekEnd, $weekKey];
    }
}
