<?php

namespace App\Jobs;

use App\Models\SmsCampaign;
use App\Models\SmsCampaignRecipient;
use App\Services\SmsCampaignService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWeeklySmsBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function __construct(
        public int $campaignId,
        public int $batchNumber,
    ) {
    }

    public function handle(SmsCampaignService $smsCampaignService): void
    {
        $campaign = SmsCampaign::query()->find($this->campaignId);

        if (! $campaign) {
            return;
        }

        $smsCampaignService->sendBatch($campaign, $this->batchNumber);
    }

    public function failed(?\Throwable $exception = null): void
    {
        SmsCampaignRecipient::query()
            ->where('sms_campaign_id', $this->campaignId)
            ->where('batch_number', $this->batchNumber)
            ->where('status', 'processing')
            ->update([
                'status' => 'failed',
                'status_text' => $exception?->getMessage() ?? 'Batch job failed unexpectedly.',
            ]);

        if ($campaign = SmsCampaign::query()->find($this->campaignId)) {
            app(SmsCampaignService::class)->refreshCampaignCounters($campaign);
        }
    }
}
