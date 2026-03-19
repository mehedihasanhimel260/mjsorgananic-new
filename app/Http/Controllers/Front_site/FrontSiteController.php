<?php

namespace App\Http\Controllers\Front_site;

use App\Http\Controllers\Controller;
use App\Models\DeliveryChargeSetting;
use App\Models\Product;
use Illuminate\Http\Request;

class FrontSiteController extends Controller
{
    public function index()
    {
        $deliverySetting = DeliveryChargeSetting::first();

        $products = Product::where('status', 'active')
            ->latest()
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->selling_price,
                    'img' => asset('images/' . $product->image),
                    'desc' => $product->short_description,
                ];
            });

        return view('front-site.index', compact('products', 'deliverySetting'));
    }
}
