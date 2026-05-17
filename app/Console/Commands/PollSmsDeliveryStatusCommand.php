<?php

namespace App\Console\Commands;

use App\Services\SmsCampaignService;
use Illuminate\Console\Command;

class PollSmsDeliveryStatusCommand extends Command
{
    protected $signature = 'sms:poll-delivery-status';

    protected $description = 'Poll MiMSMS DLR API for weekly SMS logs that are still processing.';

    public function handle(SmsCampaignService $smsCampaignService): int
    {
        $count = $smsCampaignService->pollDeliveryStatuses();

        $this->info("Polled {$count} weekly SMS log(s) for delivery status.");

        return self::SUCCESS;
    }
}
