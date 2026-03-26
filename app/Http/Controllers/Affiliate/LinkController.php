<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\AffiliateLink;
use App\Models\Product;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    public function index()
    {
        $affiliate = auth()->guard('affiliate')->user();
        $products = Product::where('status', 'active')->latest()->get();
        $links = AffiliateLink::with('product')
            ->where('affiliate_id', $affiliate->id)
            ->latest()
            ->get();

        return view('affiliates.links.index', compact('products', 'links'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $affiliate = auth()->guard('affiliate')->user();

        AffiliateLink::firstOrCreate(
            [
                'affiliate_id' => $affiliate->id,
                'product_id' => $validated['product_id'],
            ],
            [
                'tracking_code' => generate_affiliate_tracking_code(),
            ]
        );

        return redirect()->route('affiliates.links.index')->with('success', 'Affiliate link generated successfully.');
    }
}
