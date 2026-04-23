@extends('front-site.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-4">
  @include('front-site.partials.tracked-affiliate-banner')
  @include('front-site.partials.recent-orders')
  <div id="product-list" class="grid md:grid-cols-3 gap-6">
    @foreach ($products as $product)
      <div class="bg-white rounded-xl shadow hover:scale-[1.02] transition">
        <a href="{{ route('products.show', $product['id']) }}">
          <img src="{{ $product['img'] }}" class="h-56 w-full object-cover rounded-t-xl" alt="{{ $product['name'] }}">
        </a>

        <div class="p-4">
          <h3 class="font-bold text-lg">
            <a href="{{ route('products.show', $product['id']) }}" class="hover:text-green-700">
              {{ $product['name'] }}
            </a>
          </h3>
          <p class="text-green-700 font-semibold">Tk <span>{{ $product['price'] }}</span></p>
          <p class="text-sm text-gray-500 mb-3">
            <a href="{{ route('products.show', $product['id']) }}" class="hover:text-gray-700">
              {{ $product['desc'] }}
            </a>
          </p>

          <button
            data-product-id="{{ $product['id'] }}"
            class="add-to-cart-btn w-full bg-green-700 text-white py-2 rounded-lg hover:bg-green-800">
            অর্ডার করুন
          </button>
        </div>
      </div>
    @endforeach
  </div>

  @include('front-site.partials.cart-checkout')
</div>

@include('front-site.partials.visitor-modal')
@endsection

@include('front-site.partials.cart-checkout-script', ['isDetailPage' => false, 'focusedProductId' => null])

