<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\AffiliateWalletTransaction;
use App\Models\Order;
use App\Models\SteadfastSetting;
use Illuminate\Support\Facades\DB;
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

    public function bookOrder(Order $order, ?SteadfastSetting $setting = null): array
    {
        $setting ??= $this->getSetting();
        $order->loadMissing(['user', 'items.product']);

        if ($order->track_id) {
            return [
                'success' => false,
                'message' => 'Courier booking already completed for this order.',
            ];
        }

        if (! $this->hasCredentials($setting)) {
            return [
                'success' => false,
                'message' => 'Please configure Steadfast Api-Key and Secret-Key first.',
            ];
        }

        if ($order->order_status === 'cancelled') {
            return [
                'success' => false,
                'message' => 'Cancelled orders cannot be booked.',
            ];
        }

        if (! $order->user || ! $order->user->name || ! $order->user->phone || ! $order->user->saved_address) {
            return [
                'success' => false,
                'message' => 'Customer name, phone, and saved address are required for booking.',
            ];
        }

        try {
            $payload = [
                'invoice' => $order->order_number,
                'recipient_name' => $order->user->name,
                'recipient_phone' => $order->user->phone,
                'recipient_address' => $order->user->saved_address,
                'cod_amount' => $this->getGrandTotal($order),
                'item_description' => $this->getItemDescription($order),
                'total_lot' => 1,
                'delivery_type' => 0,
            ];

            $response = Http::timeout(20)
                ->withHeaders($this->headers($setting))
                ->post(self::BASE_URL.'/create_order', $payload);

            $data = $response->json();
            $responsePayload = is_array($data) ? $data : [];
            $responsePayload['item_description'] = $payload['item_description'];

            $order->update([
                'order_status' => $data['consignment']['status'] ?? $data['delivery_status'] ?? ($response->successful() ? 'submitted' : 'failed'),
                'track_id' => $data['consignment']['tracking_code'] ?? null,
                'courier_api_response' => $responsePayload,
            ]);

            return [
                'success' => ($data['status'] ?? null) === 200,
                'message' => ($data['status'] ?? null) === 200
                    ? 'Steadfast booking completed successfully.'
                    : 'Steadfast booking failed.',
                'data' => $responsePayload,
            ];
        } catch (\Throwable $exception) {
            $order->update([
                'order_status' => 'failed',
                'courier_api_response' => [
                    'status' => 500,
                    'message' => $exception->getMessage(),
                ],
            ]);

            return [
                'success' => false,
                'message' => 'Could not connect to Steadfast: '.$exception->getMessage(),
            ];
        }
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

        if ($deliveryStatus === 'cancelled') {
            $this->deductAffiliateDeliveryChargeForCancelledCourierOrder($order->fresh());
        }

        return [
            'success' => true,
            'message' => 'Order status synced successfully.',
            'data' => $data,
        ];
    }

    private function deductAffiliateDeliveryChargeForCancelledCourierOrder(Order $order): void
    {
        if ($order->order_type !== 'affiliate' || ! $order->affiliate_id) {
            return;
        }

        $deliveryCharge = (float) ($order->delivery_charge ?? 0);

        if ($deliveryCharge <= 0) {
            return;
        }

        DB::transaction(function () use ($order, $deliveryCharge) {
            $alreadyDebited = AffiliateWalletTransaction::where('affiliate_id', $order->affiliate_id)
                ->where('order_id', $order->id)
                ->where('type', 'debit')
                ->lockForUpdate()
                ->exists();

            if ($alreadyDebited) {
                return;
            }

            $affiliate = Affiliate::lockForUpdate()->find($order->affiliate_id);

            if (! $affiliate) {
                return;
            }

            AffiliateWalletTransaction::create([
                'affiliate_id' => $affiliate->id,
                'order_id' => $order->id,
                'type' => 'debit',
                'amount' => $deliveryCharge,
                'description' => 'Delivery charge deducted for cancelled courier order '.$order->order_number,
            ]);

            $affiliate->decrement('balance', $deliveryCharge);
        });
    }

    private function getGrandTotal(Order $order): float
    {
        return (float) $order->items->sum(fn ($item) => $item->quantity * $item->sell_price)
            + (float) ($order->delivery_charge ?? 0)
            - (float) ($order->discount_amount ?? 0);
    }

    private function getItemDescription(Order $order): string
    {
        return $order->items
            ->map(fn ($item) => ($item->product?->name ?? 'Product').', '.$item->quantity.'pcs')
            ->join(' | ');
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
