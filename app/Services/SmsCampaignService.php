<?php

namespace App\Services;

use App\Jobs\SendWeeklySmsBatchJob;
use App\Models\SmsCampaign;
use App\Models\SmsCampaignRecipient;
use App\Models\SmsTemplate;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class SmsCampaignService
{
    public const WEEKLY_BATCH_SIZE = 100;

    public function __construct(private readonly SmsGatewayService $smsGatewayService)
    {
    }

    public function createWeeklyCampaign(): ?SmsCampaign
    {
        $template = SmsTemplate::query()->where('is_weekly_active', true)->latest('id')->first();

        if (! $template) {
            return null;
        }

        [$weekStart, $weekEnd, $weekKey] = $this->currentWeekWindow();

        return DB::transaction(function () use ($template, $weekStart, $weekEnd, $weekKey) {
            $existing = SmsCampaign::query()
                ->where('campaign_type', 'weekly')
                ->where('week_key', $weekKey)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return null;
            }

            $campaign = SmsCampaign::query()->create([
                'sms_template_id' => $template->id,
                'title' => $template->title,
                'message' => $template->message,
                'campaign_type' => 'weekly',
                'week_key' => $weekKey,
                'week_starts_at' => $weekStart,
                'week_ends_at' => $weekEnd,
                'status' => 'queued',
                'batch_size' => self::WEEKLY_BATCH_SIZE,
            ]);

            $recipients = $this->buildRecipientSnapshot($campaign, $weekKey);

            foreach (array_chunk($recipients, 500) as $chunk) {
                SmsCampaignRecipient::query()->insert($chunk);
            }

            $this->refreshCampaignCounters($campaign);

            return $campaign->fresh();
        });
    }

    public function dispatchCampaignBatches(SmsCampaign $campaign): int
    {
        $campaign->refresh();

        $batchNumbers = SmsCampaignRecipient::query()
            ->where('sms_campaign_id', $campaign->id)
            ->whereIn('status', ['pending', 'failed'])
            ->distinct()
            ->orderBy('batch_number')
            ->pluck('batch_number');

        if ($batchNumbers->isEmpty()) {
            $this->refreshCampaignCounters($campaign);

            return 0;
        }

        $campaign->update([
            'status' => 'processing',
            'started_at' => $campaign->started_at ?? now(),
        ]);

        foreach ($batchNumbers as $batchNumber) {
            SendWeeklySmsBatchJob::dispatch($campaign->id, (int) $batchNumber);
        }

        return $batchNumbers->count();
    }

    public function sendBatch(SmsCampaign $campaign, int $batchNumber): void
    {
        $recipientIds = DB::transaction(function () use ($campaign, $batchNumber) {
            $rows = SmsCampaignRecipient::query()
                ->where('sms_campaign_id', $campaign->id)
                ->where('batch_number', $batchNumber)
                ->whereIn('status', ['pending', 'failed'])
                ->lockForUpdate()
                ->get();

            if ($rows->isEmpty()) {
                return [];
            }

            $ids = $rows->pluck('id')->all();

            SmsCampaignRecipient::query()
                ->whereIn('id', $ids)
                ->update([
                    'status' => 'processing',
                    'last_attempt_at' => now(),
                    'attempts' => DB::raw('attempts + 1'),
                ]);

            return $ids;
        });

        if ($recipientIds === []) {
            $this->refreshCampaignCounters($campaign);

            return;
        }

        $recipients = SmsCampaignRecipient::query()
            ->whereIn('id', $recipientIds)
            ->orderBy('id')
            ->get();

        $phones = $recipients->pluck('phone')->implode(',');
        $result = $this->smsGatewayService->sendBulkSms($phones, $campaign->message);

        $updatePayload = [
            'status_code' => $result['code'],
            'status_text' => $result['status_text'],
            'gateway_response' => $result['raw_response'],
            'gateway_transaction_id' => $result['transaction_id'],
        ];

        if ($result['success']) {
            SmsCampaignRecipient::query()
                ->whereIn('id', $recipientIds)
                ->update($updatePayload + [
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
        } else {
            SmsCampaignRecipient::query()
                ->whereIn('id', $recipientIds)
                ->update($updatePayload + [
                    'status' => 'failed',
                ]);
        }

        $this->refreshCampaignCounters($campaign);
    }

    public function refreshCampaignCounters(SmsCampaign $campaign): void
    {
        $base = SmsCampaignRecipient::query()->where('sms_campaign_id', $campaign->id);

        $pending = (clone $base)->where('status', 'pending')->count();
        $processing = (clone $base)->where('status', 'processing')->count();
        $sent = (clone $base)->where('status', 'sent')->count();
        $failed = (clone $base)->where('status', 'failed')->count();
        $total = (clone $base)->count();

        $status = 'queued';
        $completedAt = null;

        if ($total === 0) {
            $status = 'completed';
            $completedAt = now();
        } elseif ($processing > 0 || $pending > 0) {
            $status = 'processing';
        } elseif ($failed > 0) {
            $status = $sent > 0 ? 'partially_failed' : 'failed';
        } else {
            $status = 'completed';
            $completedAt = now();
        }

        $campaign->update([
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

    private function buildRecipientSnapshot(SmsCampaign $campaign, string $weekKey): array
    {
        $users = User::query()
            ->select('id', 'phone')
            ->where('status', 'verified')
            ->whereNotNull('phone')
            ->orderBy('id')
            ->get();

        $rows = [];
        $seenPhones = [];
        $batchNumber = 1;
        $batchIndex = 0;

        foreach ($users as $user) {
            $normalizedPhone = $this->smsGatewayService->normalizePhone($user->phone);

            if (! $normalizedPhone || isset($seenPhones[$normalizedPhone])) {
                continue;
            }

            $seenPhones[$normalizedPhone] = true;

            $rows[] = [
                'sms_campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'phone' => $normalizedPhone,
                'week_key' => $weekKey,
                'batch_number' => $batchNumber,
                'status' => 'pending',
                'attempts' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $batchIndex++;

            if ($batchIndex === self::WEEKLY_BATCH_SIZE) {
                $batchIndex = 0;
                $batchNumber++;
            }
        }

        return $rows;
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
