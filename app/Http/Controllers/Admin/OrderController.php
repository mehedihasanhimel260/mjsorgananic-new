<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SteadfastSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    private const STEADFAST_BASE_URL = 'https://portal.packzy.com/api/v1';

    private function getGrandTotal(Order $order): float
    {
        return (float) $order->items->sum(fn($item) => $item->quantity * $item->sell_price)
            + (float) ($order->delivery_charge ?? 0)
            - (float) ($order->discount_amount ?? 0);
    }

    private function getItemDescription(Order $order): string
    {
        return $order->items
            ->map(fn($item) => ($item->product?->name ?? 'Product').', '.$item->quantity.'pcs')
            ->join(' | ');
    }

    public function index()
    {
        $orders = Order::with(['user', 'affiliate', 'items.product'])
            ->latest()
            ->get();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'affiliate', 'items.product', 'stockOutLogs.batch']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateDiscount(Request $request, Order $order)
    {
        $validated = $request->validate([
            'discount_amount' => 'required|numeric|min:0',
        ]);

        $order->update([
            'discount_amount' => $validated['discount_amount'],
        ]);

        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Discount updated successfully.');
    }

    public function bookCourier(Order $order)
    {
        $order->loadMissing(['user', 'items.product']);

        if ($order->track_id) {
            return redirect()->route('admin.orders.show', $order->id)->with('error', 'Courier booking already completed for this order.');
        }

        $setting = SteadfastSetting::first();

        if (! $setting || ! $setting->api_key || ! $setting->secret_key) {
            return redirect()->route('admin.orders.show', $order->id)->with('error', 'Please configure Steadfast Api-Key and Secret-Key first.');
        }

        if (! $order->user || ! $order->user->name || ! $order->user->phone || ! $order->user->saved_address) {
            return redirect()->route('admin.orders.show', $order->id)->with('error', 'Customer name, phone, and saved address are required for booking.');
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
                ->withHeaders([
                    'Api-Key' => $setting->api_key,
                    'Secret-Key' => $setting->secret_key,
                    'Content-Type' => 'application/json',
                ])
                ->post(self::STEADFAST_BASE_URL.'/create_order', $payload);

            $data = $response->json();
            $responsePayload = is_array($data) ? $data : [];
            $responsePayload['item_description'] = $payload['item_description'];

            $order->update([
                'order_status' => $data['consignment']['status'] ?? $data['delivery_status'] ?? ($response->successful() ? 'submitted' : 'failed'),
                'track_id' => $data['consignment']['tracking_code'] ?? null,
                'courier_api_response' => $responsePayload,
            ]);

            if (($data['status'] ?? null) === 200) {
                return redirect()->route('admin.orders.show', $order->id)->with('success', 'Steadfast booking completed successfully.');
            }

            return redirect()->route('admin.orders.show', $order->id)->with('error', 'Steadfast booking failed.');
        } catch (\Throwable $exception) {
            $order->update([
                'order_status' => 'failed',
                'courier_api_response' => [
                    'status' => 500,
                    'message' => $exception->getMessage(),
                ],
            ]);

            return redirect()->route('admin.orders.show', $order->id)->with('error', 'Could not connect to Steadfast: '.$exception->getMessage());
        }
    }
}
