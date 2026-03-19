<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStockBatch;
use Illuminate\Http\Request;

class ProductStockBatchController extends Controller
{
    public function index()
    {
        $stockBatches = ProductStockBatch::with('product')->latest()->get();
        return view('admin.product_stock.index', compact('stockBatches'));
    }

    public function create()
    {
        $products = Product::all();
        return view('admin.product_stock.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'total_cost' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $validated['cost_per_unit'] = $validated['total_cost'] / $validated['quantity'];

        ProductStockBatch::create($validated);

        return redirect()->route('admin.product-stocks.index')->with('success', 'Stock batch created successfully.');
    }

    public function edit(ProductStockBatch $product_stock)
    {
        $products = Product::all();
        return view('admin.product_stock.edit', compact('product_stock', 'products'));
    }

    public function update(Request $request, ProductStockBatch $product_stock)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'total_cost' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $validated['cost_per_unit'] = $validated['total_cost'] / $validated['quantity'];

        $product_stock->update($validated);

        return redirect()->route('admin.product-stocks.index')->with('success', 'Stock batch updated successfully.');
    }

    public function destroy(ProductStockBatch $product_stock)
    {
        $product_stock->delete();
        return redirect()->route('admin.product-stocks.index')->with('success', 'Stock batch deleted successfully.');
    }
}