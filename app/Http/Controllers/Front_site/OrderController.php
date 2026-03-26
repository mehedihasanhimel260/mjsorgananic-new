<?php

namespace App\Http\Controllers\Front_site;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AffiliateWalletTransaction;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCommission;
use App\Models\ProductStockBatch;
use App\Models\StockOutLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    private function getAffiliateAttribution(Request $request): ?array
    {
        return get_affiliate_attribution($request);
    }

    private function applyAffiliateToCart($cart, ?array $attribution): void
    {
        if (! $cart || ! $attribution || empty($attribution['affiliate_id'])) {
            return;
        }

        if (! $cart->affiliate_id) {
            $cart->update([
                'affiliate_id' => $attribution['affiliate_id'],
            ]);
        }
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

    private function calculateCommissionAmount(string $commissionType, float $commissionValue, int $quantity, float $sellPrice): float
    {
        if ($commissionType === 'fixed') {
            return round($commissionValue * $quantity, 2);
        }

        if ($commissionType === 'percent') {
            return round((($sellPrice * $quantity) * $commissionValue) / 100, 2);
        }

        return 0;
    }

    private function createAffiliateCommissionEntries(Order $order): void
    {
        if (! $order->affiliate_id || $order->order_type !== 'affiliate') {
            return;
        }

        $affiliate = Affiliate::lockForUpdate()->find($order->affiliate_id);

        if (! $affiliate) {
            return;
        }

        $totalCommission = 0;

        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            $commissionRule = ProductCommission::where('product_id', $item->product_id)
                ->where('status', 'active')
                ->latest('id')
                ->first();

            if (! $commissionRule) {
                continue;
            }

            $commissionAmount = $this->calculateCommissionAmount(
                $commissionRule->commission_type,
                (float) $commissionRule->commission_value,
                (int) $item->quantity,
                (float) $item->sell_price
            );

            if ($commissionAmount <= 0) {
                continue;
            }

            AffiliateCommission::create([
                'affiliate_id' => $affiliate->id,
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'commission_type' => $commissionRule->commission_type,
                'commission_value' => $commissionRule->commission_value,
                'commission_amount' => $commissionAmount,
            ]);

            $totalCommission += $commissionAmount;
        }

        if ($totalCommission <= 0) {
            return;
        }

        AffiliateWalletTransaction::create([
            'affiliate_id' => $affiliate->id,
            'type' => 'credit',
            'amount' => $totalCommission,
            'description' => 'Commission credited for order '.$order->order_number,
        ]);

        $affiliate->increment('balance', $totalCommission);
    }

    private function buildVisitorMeta(Request $request): array
    {
        return [
            'ip_address' => $request->ip(),
            'last_user_agent' => substr((string) $request->userAgent(), 0, 65535),
            'last_visit_at' => now(),
            'last_logged_at' => now(),
        ];
    }

    private function buildLocationData(array $validated): array
    {
        $locationPermission = $validated['location_permission'] ?? 'unknown';

        return [
            'location_permission' => $locationPermission,
            'gps_lat' => $locationPermission === 'granted' ? ($validated['gps_lat'] ?? null) : null,
            'gps_lng' => $locationPermission === 'granted' ? ($validated['gps_lng'] ?? null) : null,
            'gps_address' => $locationPermission === 'granted' ? ($validated['gps_address'] ?? null) : null,
        ];
    }

    private function storeVisitorMetaInSession(Request $request): array
    {
        $meta = $this->buildVisitorMeta($request);
        $request->session()->put('visitor_meta', $meta);

        return $meta;
    }

    private function getCartFromSession(Request $request)
    {
        $cart = null;
        if ($request->session()->has('cart_id')) {
            $cart = Cart::find($request->session()->get('cart_id'));
        } elseif ($request->session()->has('user_id')) {
            $cart = Cart::where('user_id', $request->session()->get('user_id'))->where('status', 'pending')->first();
        }

        $this->applyAffiliateToCart($cart, $this->getAffiliateAttribution($request));

        return $cart;
    }

    private function getCartData($cart)
    {
        if (! $cart) {
            return [
                'items' => [],
                'product_total' => 0,
            ];
        }

        $cart->load('items.product');

        $items = $cart->items->map(function ($item) {
            return [
                'id' => $item->product_id,
                'name' => $item->product->name,
                'price' => (float) $item->sell_price,
                'qty' => $item->quantity,
                'img' => asset('images/'.$item->product->image),
                'desc' => $item->product->short_description,
            ];
        });

        $productTotal = $items->sum(function ($item) {
            return $item['price'] * $item['qty'];
        });

        return [
            'items' => $items,
            'product_total' => $productTotal,
        ];
    }

    public function getCart(Request $request)
    {
        $cart = $this->getCartFromSession($request);

        return response()->json($this->getCartData($cart));
    }

    public function visitorPing(Request $request)
    {
        $meta = $this->storeVisitorMetaInSession($request);

        if ($request->session()->has('user_id')) {
            $user = User::find($request->session()->get('user_id'));

            if ($user) {
                $user->update($meta);
            }
        }

        return response()->json([
            'success' => true,
            'ip_address' => $meta['ip_address'],
            'visited_at' => Carbon::parse($meta['last_visit_at'])->toDateTimeString(),
        ]);
    }

    public function registerVisitor(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'location_permission' => ['nullable', Rule::in(['granted', 'denied', 'prompt', 'unknown'])],
            'gps_lat' => 'nullable|numeric|between:-90,90',
            'gps_lng' => 'nullable|numeric|between:-180,180',
            'gps_address' => 'nullable|string|max:1000',
        ]);

        $sessionMeta = $request->session()->get('visitor_meta', []);
        $visitorMeta = array_merge($sessionMeta, $this->buildVisitorMeta($request));
        $locationData = $this->buildLocationData($validated);

        $user = User::where('phone', $validated['phone'])->first();

        if ($user) {
            $user->update(array_merge([
                'name' => $validated['name'],
            ], $visitorMeta, $locationData));
        } else {
            $user = User::create(array_merge([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'password' => Hash::make('visitor-'.uniqid()),
            ], $visitorMeta, $locationData));
        }

        session([
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_phone' => $user->phone,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Visitor information saved successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'ip_address' => $user->ip_address,
                'location_permission' => $user->location_permission,
                'saved_address' => $user->saved_address,
            ],
        ]);
    }

    public function addToCart(Request $request)
    {

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
        ]);

        $user = null;
        if ($request->has('phone')) {
            $user = User::updateOrCreate(
                ['phone' => $validated['phone']],
                array_merge([
                    'name' => $validated['name'],
                ], $request->session()->get('visitor_meta', []), $this->buildVisitorMeta($request))
            );
            session(['user_id' => $user->id, 'customer_name' => $user->name, 'customer_phone' => $user->phone]);
        } elseif ($request->session()->has('user_id')) {
            $user = User::find($request->session()->get('user_id'));
        }

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'User not found.'], 404);
            }

            return redirect()->back()->with('error', 'User not found.');
        }

        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'pending'],
            ['session_id' => session()->getId(), 'cart_type' => 'user']
        );
        $this->applyAffiliateToCart($cart, $this->getAffiliateAttribution($request));
        session(['cart_id' => $cart->id]);

        $product = Product::findOrFail($validated['product_id']);

        $cartItem = $cart->items()->where('product_id', $product->id)->first();

        if ($cartItem) {
            $cartItem->increment('quantity');
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => 1,
                'sell_price' => $product->selling_price,
            ]);
        }

        if ($request->expectsJson()) {
            $updatedCart = $this->getCartFromSession($request);

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully.',
                'cart' => $this->getCartData($updatedCart),
            ]);
        }

        return redirect()->back()->with('success', 'Product added to cart successfully.');
    }

    public function updateCartQuantity(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'action' => 'required|string|in:increment,decrement',
        ]);

        $cart = $this->getCartFromSession($request);

        if (! $cart) {
            return response()->json(['success' => false, 'error' => 'Cart not found.'], 404);
        }

        $cartItem = $cart->items()->where('product_id', $validated['product_id'])->first();

        if (! $cartItem) {
            return response()->json(['success' => false, 'error' => 'Product not in cart.'], 404);
        }

        if ($validated['action'] === 'increment') {
            $cartItem->increment('quantity');
        } elseif ($validated['action'] === 'decrement') {
            if ($cartItem->quantity > 1) {
                $cartItem->decrement('quantity');
            } else {
                $cartItem->delete();
            }
        }

        // We need to load the cart again to get the fresh data after update
        $updatedCart = $this->getCartFromSession($request);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully.',
            'cart' => $this->getCartData($updatedCart),
        ]);
    }

    public function completeOrder(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:2000',
            'selected_delivery_charge' => 'nullable|numeric|min:0',
        ]);

        $user = User::where('phone', $validated['phone'])->first();

        if (! $user && $request->session()->has('user_id')) {
            $user = User::find($request->session()->get('user_id'));
        }

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found. Please save visitor information first.',
                ], 404);
            }

            return redirect()->back()->with('error', 'User not found. Please save visitor information first.');
        }

        $cart = $this->getCartFromSession($request);

        if (! $cart || $cart->items()->count() === 0) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart is empty.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Cart is empty.');
        }

        $cart->load('items.product');

        try {
            $order = DB::transaction(function () use ($request, $validated, $user, $cart) {
            $affiliateId = $cart->affiliate_id ?: ($this->getAffiliateAttribution($request)['affiliate_id'] ?? null);
            $user->update(array_merge([
                'name' => $validated['name'],
                'saved_address' => $validated['address'],
            ], $this->buildVisitorMeta($request)));

            $totalAmount = $cart->items->sum(function ($item) {
                return $item->quantity * $item->sell_price;
            });

            $order = Order::create([
                'order_number' => 'ORD-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
                'user_id' => $user->id,
                'affiliate_id' => $affiliateId,
                'order_type' => $affiliateId ? 'affiliate' : 'direct',
                'total_amount' => $totalAmount,
                'delivery_charge' => $validated['selected_delivery_charge'] ?? 0,
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'sell_price' => $item->sell_price,
                ]);

                $this->deductProductStock($item->product_id, $item->quantity, $order->id);
            }

            $this->createAffiliateCommissionEntries($order);

            $cart->update(['status' => 'converted']);
            $cart->items()->delete();

            return $order;
        });
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                throw $exception;
            }

            return redirect()->back()->with('error', $exception->validator->errors()->first());
        }

        $request->session()->forget('cart_id');
        session([
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_phone' => $user->phone,
        ]);

        if (! $request->expectsJson()) {
            return redirect()->route('home')->with('success', 'Order completed successfully. Order No: '.$order->order_number);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order completed successfully.',
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
            ],
        ]);
    }
}
