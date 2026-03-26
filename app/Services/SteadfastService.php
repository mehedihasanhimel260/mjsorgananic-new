<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SteadfastSetting;
use Illuminate\Support\Facades\Http;

class SteadfastService
{
    private const BASE_URL = 'https://portal.packzy.com/api/v1';

    public function getSetting(): SteadfastSetting
    {
        return SteadfastSetting::firstOrCreate([], [
            'current_balance' => 0,
        ]);
    }

    public function hasCredentials(?SteadfastSetting $setting = null): bool
    {
        $setting ??= $this->getSetting();

        return ! empty($setting->api_key) && ! empty($setting->secret_key);
    }

    public function refreshBalance(?SteadfastSetting $setting = null): array
    {
        $setting ??= $this->getSetting();

        if (! $this->hasCredentials($setting)) {
            return [
                'success' => false,
                'message' => 'Please save Api-Key and Secret-Key first.',
            ];
        }

        $response = Http::timeout(20)
            ->withHeaders($this->headers($setting))
            ->get(self::BASE_URL.'/get_balance');

        if (! $response->successful()) {
            return [
                'success' => false,
                'message' => 'Steadfast balance refresh failed.',
            ];
        }

        $data = $response->json();

        if (! isset($data['current_balance'])) {
            return [
                'success' => false,
                'message' => 'Invalid balance response from Steadfast.',
            ];
        }

        $setting->update([
            'current_balance' => $data['current_balance'],
            'last_balance_synced_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Steadfast balance refreshed successfully.',
            'data' => $data,
        ];
    }

    public function syncOrderStatus(Order $order, ?SteadfastSetting $setting = null): array
    {
        $setting ??= $this->getSetting();

        if (! $this->hasCredentials($setting)) {
            return [
                'success' => false,
                'message' => 'Please configure Steadfast credentials first.',
            ];
        }

        $consignmentId = data_get($order->courier_api_response, 'consignment.consignment_id');

        if (! $consignmentId) {
            return [
                'success' => false,
                'message' => 'Consignment ID not found for this order.',
            ];
        }

        $response = Http::timeout(20)
            ->withHeaders($this->headers($setting))
            ->get(self::BASE_URL.'/status_by_cid/'.$consignmentId);

        if (! $response->successful()) {
            return [
                'success' => false,
                'message' => 'Steadfast status check failed.',
            ];
        }

        $data = $response->json();
        $deliveryStatus = $data['delivery_status'] ?? null;

        if (! $deliveryStatus) {
            return [
                'success' => false,
                'message' => 'Invalid delivery status response from Steadfast.',
            ];
        }

        $courierResponse = $order->courier_api_response ?? [];
        $courierResponse['delivery_status_check'] = $data;

        $order->update([
            'order_status' => $deliveryStatus,
            'courier_api_response' => $courierResponse,
        ]);

        return [
            'success' => true,
            'message' => 'Order status synced successfully.',
            'data' => $data,
        ];
    }

    private function headers(SteadfastSetting $setting): array
    {
        return [
            'Api-Key' => $setting->api_key,
            'Secret-Key' => $setting->secret_key,
            'Content-Type' => 'application/json',
        ];
    }
}
