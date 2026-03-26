<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\SteadfastService;
use Illuminate\Console\Command;

class SyncSteadfastStatusCommand extends Command
{
    protected $signature = 'steadfast:sync-status';

    protected $description = 'Sync Steadfast order statuses and current balance';

    public function handle(SteadfastService $steadfastService): int
    {
        $setting = $steadfastService->getSetting();

        if (! $steadfastService->hasCredentials($setting)) {
            $this->warn('Steadfast credentials are missing.');

            return self::SUCCESS;
        }

        $orders = Order::query()
            ->whereNotNull('order_status')
            ->whereNotIn('order_status', ['cancelled', 'partial_delivered', 'delivered'])
            ->whereNotNull('courier_api_response')
            ->get()
            ->filter(fn ($order) => (bool) data_get($order->courier_api_response, 'consignment.consignment_id'));

        $synced = 0;
        $failed = 0;

        foreach ($orders as $order) {
            try {
                $result = $steadfastService->syncOrderStatus($order, $setting);

                if ($result['success']) {
                    $synced++;
                } else {
                    $failed++;
                }
            } catch (\Throwable $exception) {
                $failed++;
                $this->error('Order #'.$order->id.' sync failed: '.$exception->getMessage());
            }
        }

        try {
            $steadfastService->refreshBalance($setting);
        } catch (\Throwable $exception) {
            $this->error('Balance sync failed: '.$exception->getMessage());
        }

        $this->info("Steadfast sync completed. Synced: {$synced}, Failed: {$failed}");

        return self::SUCCESS;
    }
}
