<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCommission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductCommissionController extends Controller
{
    private function syncActiveCommission(int $productId, ?int $ignoreId = null): void
    {
        ProductCommission::where('product_id', $productId)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->update(['status' => 'inactive']);
    }

    public function index()
    {
        $commissions = ProductCommission::with('product')
            ->latest()
            ->get();

        return view('admin.product-commissions.index', compact('commissions'));
    }

    public function create(Request $request)
    {
        $products = Product::where('status', 'active')->latest()->get();
        $selectedProductId = $request->query('product_id');

        return view('admin.product-commissions.create', compact('products', 'selectedProductId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'commission_type' => 'required|in:fixed,percent',
            'commission_value' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        DB::transaction(function () use ($validated) {
            if ($validated['status'] === 'active') {
                $this->syncActiveCommission((int) $validated['product_id']);
            }

            ProductCommission::create($validated);
        });

        return redirect()->route('admin.product-commissions.index')->with('success', 'Product commission created successfully.');
    }

    public function edit(ProductCommission $productCommission)
    {
        $products = Product::where('status', 'active')->latest()->get();

        return view('admin.product-commissions.edit', compact('productCommission', 'products'));
    }

    public function update(Request $request, ProductCommission $productCommission)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'commission_type' => 'required|in:fixed,percent',
            'commission_value' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        DB::transaction(function () use ($validated, $productCommission) {
            if ($validated['status'] === 'active') {
                $this->syncActiveCommission((int) $validated['product_id'], $productCommission->id);
            }

            $productCommission->update($validated);
        });

        return redirect()->route('admin.product-commissions.index')->with('success', 'Product commission updated successfully.');
    }

    public function toggleStatus(ProductCommission $productCommission)
    {
        DB::transaction(function () use ($productCommission) {
            $newStatus = $productCommission->status === 'active' ? 'inactive' : 'active';

            if ($newStatus === 'active') {
                $this->syncActiveCommission($productCommission->product_id, $productCommission->id);
            }

            $productCommission->update([
                'status' => $newStatus,
            ]);
        });

        return redirect()->route('admin.product-commissions.index')->with('success', 'Commission status updated successfully.');
    }
}
