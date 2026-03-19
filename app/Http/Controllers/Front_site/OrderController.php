<?php

namespace App\Http\Controllers\Front_site;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private function getCartFromSession(Request $request)
    {
        $cart = null;
        if ($request->session()->has('cart_id')) {
            $cart = Cart::find($request->session()->get('cart_id'));
        } elseif ($request->session()->has('user_id')) {
            $cart = Cart::where('user_id', $request->session()->get('user_id'))->where('status', 'pending')->first();
        }

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
                ['name' => $validated['name']]
            );
            session(['user_id' => $user->id, 'customer_name' => $user->name, 'customer_phone' => $user->phone]);
        } elseif ($request->session()->has('user_id')) {
            $user = User::find($request->session()->get('user_id'));
        }

        if (! $user) {
            return response()->json(['success' => false, 'error' => 'User not found.'], 400);
        }

        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'pending'],
            ['session_id' => session()->getId(), 'cart_type' => 'user']
        );
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

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully.',
            'cart' => $this->getCartData($cart),
        ]);
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
}
