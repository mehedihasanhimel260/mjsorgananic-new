<?php

namespace App\Http\Controllers\Front_site;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\Order;
use App\Models\ProductStockBatch;
use App\Models\StockOutLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LandingPageController extends Controller
{
    public function home(FrontSiteController $frontSiteController)
    {
        $landingPage = LandingPage::with(['products' => function ($query) {
            $query->where('status', 'active');
        }])
            ->where('status', 'active')
            ->latest('updated_at')
            ->first();

        if (! $landingPage || $landingPage->products->isEmpty()) {
            return $frontSiteController->index();
        }

        $product = $landingPage->primaryProduct();

        return view('front-site.landing.show', compact('landingPage', 'product'));
    }

    public function show(string $slug)
    {
        $landingPage = LandingPage::with(['products' => function ($query) {
            $query->where('status', 'active');
        }])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        abort_if($landingPage->products->isEmpty(), 404);

        $product = $landingPage->primaryProduct();

        return view('front-site.landing.show', compact('landingPage', 'product'));
    }

    public function order(Request $request, LandingPage $landingPage)
    {
        $landingPage->load(['products' => function ($query) {
            $query->where('status', 'active');
        }]);

        abort_if($landingPage->status !== 'active' || $landingPage->products->isEmpty(), 404);

        $product = $landingPage->products->firstWhere('id', (int) $request->input('product_id')) ?: $landingPage->primaryProduct();

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:999',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:2000',
        ]);

        if (! $landingPage->products->contains('id', (int) $validated['product_id'])) {
            return back()->withInput()->with('error', 'Invalid product selected for this landing page.');
        }

        $phone = $this->normalizeBangladeshPhone($validated['phone']);
        if (! $phone) {
            return back()->withInput()->with('error', 'Please enter a valid 11 digit phone number.');
        }

        try {
            $order = DB::transaction(function () use ($request, $validated, $phone, $product) {
                $user = User::firstOrNew(['phone' => $phone]);
                $user->fill([
                    'name' => $validated['name'],
                    'saved_address' => $validated['address'],
                    'ip_address' => $request->ip(),
                    'last_user_agent' => substr((string) $request->userAgent(), 0, 65535),
                    'last_visit_at' => now(),
                    'last_logged_at' => now(),
                ]);

                if (! $user->exists) {
                    $user->password = Hash::make('landing-'.uniqid());
                }

                $user->save();

                $quantity = (int) $validated['quantity'];
                $sellPrice = (float) $product->selling_price;
                $totalAmount = $quantity * $sellPrice;
                $affiliateId = get_affiliate_attribution($request)['affiliate_id'] ?? null;

                $order = Order::create([
                    'order_number' => 'ORD-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
                    'user_id' => $user->id,
                    'affiliate_id' => $affiliateId,
                    'order_type' => $affiliateId ? 'affiliate' : 'direct',
                    'total_amount' => $totalAmount,
                    'delivery_charge' => 0,
                    'order_status' => 'pending',
                ]);

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'sell_price' => $sellPrice,
                ]);

                $this->deductProductStock($product->id, $quantity, $order->id);

                session([
                    'user_id' => $user->id,
                    'customer_name' => $user->name,
                    'customer_phone' => $user->phone,
                ]);

                return $order;
            });
        } catch (ValidationException $exception) {
            return back()->withInput()->with('error', $exception->validator->errors()->first());
        }

        return redirect()
            ->route('landing.show', $landingPage->slug)
            ->with('success', 'ধন্যবাদ। আপনার অর্ডারটি গ্রহণ করা হয়েছে। আমাদের প্রতিনিধি অর্ডারটি নিশ্চিত করবেন। Order No: '.$order->order_number);
    }

    private function normalizeBangladeshPhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (! $digits) {
            return null;
        }

        if (strlen($digits) >= 11) {
            $digits = substr($digits, -11);
        }

        if (strlen($digits) !== 11 || ! str_starts_with($digits, '01')) {
            return null;
        }

        return $digits;
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
                'stock' => ["Insufficient stock. Available: {$availableQuantity}, required: {$requiredQuantity}."],
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
}
