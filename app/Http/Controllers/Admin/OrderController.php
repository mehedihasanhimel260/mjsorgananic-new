<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryChargeSetting;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStockBatch;
use App\Models\StockOutLog;
use App\Services\SteadfastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(private readonly SteadfastService $steadfastService)
    {
    }

    private function deductProductStock(int $productId, int $requiredQuantity, int $orderId): void
    {
        $batches = ProductStockBatch::where('product_id', $productId)
            ->where('quantity', '>', 0)
            ->orderBy('created_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $availableQuantity = $batches->sum('quantity');

        if ($availableQuantity < $requiredQuantity) {
            throw ValidationException::withMessages([
                'stock' => ["Insufficient stock for product ID {$productId}. Available: {$availableQuantity}, required: {$requiredQuantity}."],
            ]);
        }

        $remaining = $requiredQuantity;

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $usedQuantity = min($remaining, $batch->quantity);

            $batch->decrement('quantity', $usedQuantity);

            StockOutLog::create([
                'product_id' => $productId,
                'batch_id' => $batch->id,
                'order_id' => $orderId,
                'quantity' => $usedQuantity,
                'cost_per_unit' => $batch->cost_per_unit,
            ]);

            $remaining -= $usedQuantity;
        }
    }

    private function restoreOrderStock(Order $order): void
    {
        $logs = StockOutLog::where('order_id', $order->id)
            ->lockForUpdate()
            ->get();

        foreach ($logs as $log) {
            $batch = ProductStockBatch::where('id', $log->batch_id)->lockForUpdate()->first();

            if ($batch) {
                $batch->increment('quantity', $log->quantity);
            }
        }

        StockOutLog::where('order_id', $order->id)->delete();
    }

    private function resyncOrderStockAndTotals(Order $order): void
    {
        $order->load('items');

        $this->restoreOrderStock($order);

        foreach ($order->items as $item) {
            $this->deductProductStock($item->product_id, $item->quantity, $order->id);
        }

        $order->update([
            'total_amount' => $order->items->sum(fn ($item) => $item->quantity * $item->sell_price),
        ]);
    }

    public function index()
    {
        $orders = Order::with(['user', 'affiliate', 'items.product'])
            ->orderByRaw("CASE WHEN order_status IS NULL OR order_status = 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->get();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'affiliate', 'items.product', 'stockOutLogs.batch']);
        $deliverySetting = DeliveryChargeSetting::first();
        $products = Product::where('status', 'active')->latest()->get();

        return view('admin.orders.show', compact('order', 'products', 'deliverySetting'));
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

    public function updateDeliveryCharge(Request $request, Order $order)
    {
        $deliverySetting = DeliveryChargeSetting::first();

        if (! $deliverySetting) {
            return redirect()->route('admin.orders.show', $order->id)->with('error', 'Delivery charge settings not found.');
        }

        $validated = $request->validate([
            'delivery_location' => 'required|in:inside_dhaka,outside_dhaka',
        ]);

        $deliveryCharge = $validated['delivery_location'] === 'inside_dhaka'
            ? (float) $deliverySetting->inside_dhaka_delivery_charge
            : (float) $deliverySetting->outside_dhaka_delivery_charge;

        $order->update([
            'delivery_charge' => $deliveryCharge,
        ]);

        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Delivery charge updated successfully.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        if ($order->track_id) {
            return redirect()->route('admin.orders.show', $order->id)->with('error', 'Booked courier orders cannot be manually cancelled from here.');
        }

        $validated = $request->validate([
            'order_status' => 'required|in:cancelled',
        ]);

        $order->update([
            'order_status' => $validated['order_status'],
        ]);

        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Order status updated successfully.');
    }

    public function updateItem(Request $request, Order $order, int $itemId)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($order, $itemId, $validated) {
                $item = $order->items()->lockForUpdate()->findOrFail($itemId);
                $item->update([
                    'quantity' => $validated['quantity'],
                ]);

                $this->resyncOrderStockAndTotals($order->fresh());
            });
        } catch (ValidationException $exception) {
            return redirect()->route('admin.orders.show', $order->id)->with('error', $exception->validator->errors()->first());
        }

        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Product quantity updated successfully.');
    }

    public function addItem(Request $request, Order $order)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($order, $validated) {
                $product = Product::findOrFail($validated['product_id']);
                $item = $order->items()->lockForUpdate()->where('product_id', $product->id)->first();

                if ($item) {
                    $item->increment('quantity', $validated['quantity']);
                } else {
                    $order->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $validated['quantity'],
                        'sell_price' => $product->selling_price,
                    ]);
                }

                $this->resyncOrderStockAndTotals($order->fresh());
            });
        } catch (ValidationException $exception) {
            return redirect()->route('admin.orders.show', $order->id)->with('error', $exception->validator->errors()->first());
        }

        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Product added to order successfully.');
    }

    public function removeItem(Order $order, int $itemId)
    {
        if ($order->items()->count() <= 1) {
            return redirect()->route('admin.orders.show', $order->id)->with('error', 'At least one product must remain in the order.');
        }

        DB::transaction(function () use ($order, $itemId) {
            $item = $order->items()->lockForUpdate()->findOrFail($itemId);
            $item->delete();

            $this->resyncOrderStockAndTotals($order->fresh());
        });

        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Product removed from order successfully.');
    }

    public function bookCourier(Order $order)
    {
        $result = $this->steadfastService->bookOrder($order);

        return redirect()->route('admin.orders.show', $order->id)
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function syncCourierStatus(Order $order)
    {
        try {
            $result = $this->steadfastService->syncOrderStatus($order);

            return redirect()->route('admin.orders.show', $order->id)
                ->with($result['success'] ? 'success' : 'error', $result['message']);
        } catch (\Throwable $exception) {
            return redirect()->route('admin.orders.show', $order->id)->with('error', 'Could not sync courier status: '.$exception->getMessage());
        }
    }
}
