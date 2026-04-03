<?php

namespace App\Http\Controllers\Front_site;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\DeliveryChargeSetting;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class FrontSiteController extends Controller
{
    private function mapProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->selling_price,
            'img' => asset('images/'.$product->image),
            'desc' => $product->short_description,
            'long_desc' => $product->long_description,
            'sku' => $product->sku,
            'keywords' => $product->keywords,
        ];
    }

    private function getFrontContext(): array
    {
        $deliverySetting = DeliveryChargeSetting::first();

        $products = Product::where('status', 'active')
            ->latest()
            ->get()
            ->map(fn ($product) => $this->mapProduct($product));

        $affiliateTracking = get_affiliate_attribution(request());
        $trackedAffiliate = null;

        if (! empty($affiliateTracking['affiliate_id'])) {
            $trackedAffiliate = Affiliate::find($affiliateTracking['affiliate_id']);
        }

        $recentOrders = collect();
        $sessionUserId = session('user_id');

        if ($sessionUserId) {
            $user = User::find($sessionUserId);

            if ($user) {
                $recentOrders = Order::with(['items.product'])
                    ->where('user_id', $user->id)
                    ->latest()
                    ->take(3)
                    ->get();
            }
        }

        return compact('products', 'deliverySetting', 'trackedAffiliate', 'recentOrders');
    }

    public function index()
    {
        return view('front-site.index', $this->getFrontContext());
    }

    public function show(Product $product)
    {
        abort_unless($product->status === 'active', 404);

        $context = $this->getFrontContext();
        $context['product'] = $this->mapProduct($product);

        return view('front-site.show', $context);
    }
}
