<?php

namespace App\Jobs;

use App\Models\SmsCampaign;
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

    /**
     * @param  array<int, array{user_id:int,phone:string}>  $recipients
     */
    public function __construct(
        public int $campaignId,
        public array $recipients,
    ) {}

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(SmsCampaignService $smsCampaignService): void
    {
        $campaign = SmsCampaign::query()->find($this->campaignId);

        if (! $campaign || $this->recipients === []) {
            return;
        }

        $smsCampaignService->sendBatch($campaign, $this->recipients);
    }

    public function failed(?\Throwable $exception = null): void
    {
        if ($campaign = SmsCampaign::query()->find($this->campaignId)) {
            app(SmsCampaignService::class)->refreshCampaignCounters($campaign);
        }
    }
}
