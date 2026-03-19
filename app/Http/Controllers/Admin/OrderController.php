<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
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
}
