<?php

namespace App\Console\Commands;

use App\Services\SmsCampaignService;
use Illuminate\Console\Command;

class DispatchWeeklySmsCampaignCommand extends Command
{
    protected $signature = 'sms:dispatch-weekly-campaign';

    protected $description = 'Create and dispatch the weekly SMS campaign from the active template.';

    public function handle(SmsCampaignService $smsCampaignService): int
    {
        $campaign = $smsCampaignService->createWeeklyCampaign();

        if (! $campaign) {
            $this->info('No active weekly SMS template found or campaign already exists for this week.');

            return self::SUCCESS;
        }

        $jobs = $smsCampaignService->dispatchCampaignBatches($campaign);

        $this->info("Weekly SMS campaign #{$campaign->id} dispatched with {$jobs} batch job(s).");

        return self::SUCCESS;
    }
}
