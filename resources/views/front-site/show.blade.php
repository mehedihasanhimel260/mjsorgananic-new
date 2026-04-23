@extends('front-site.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-4">
  @include('front-site.partials.tracked-affiliate-banner')


  <div class="grid gap-8 lg:grid-cols-2 mb-10">
    <div class="bg-white rounded-2xl shadow overflow-hidden">
      <img src="{{ $product['img'] }}" class="w-full h-[420px] object-cover" alt="{{ $product['name'] }}">
    </div>

    <div class="bg-white rounded-2xl shadow p-6 lg:p-8">
      <p class="text-sm font-semibold tracking-[0.2em] text-green-700 uppercase mb-3">পণ্যের বিবরণ</p>
      <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-3">{{ $product['name'] }}</h1>
      <p class="text-2xl font-bold text-green-700 mb-4">Tk {{ $product['price'] }}</p>

      @if(!empty($product['sku']))
        <p class="text-sm text-gray-500 mb-2">এসকেইউ: {{ $product['sku'] }}</p>
      @endif

      @if(!empty($product['keywords']))
        <p class="text-sm text-gray-500 mb-4">কীওয়ার্ড: {{ $product['keywords'] }}</p>
      @endif

      <div class="prose max-w-none text-gray-700 mb-6">
        <p class="mb-4">{{ $product['desc'] }}</p>
        @if(!empty($product['long_desc']))
          @php
            $longDescriptionText = trim(strip_tags($product['long_desc']));
            $needsReadMore = \Illuminate\Support\Str::length($longDescriptionText) > 200;
            $previewText = $needsReadMore ? \Illuminate\Support\Str::limit($longDescriptionText, 200) : $longDescriptionText;
          @endphp

          @if($needsReadMore)
            <div id="long-desc-preview" class="text-gray-700 leading-7">{{ $previewText }}</div>
            <div id="long-desc-full" class="text-gray-700 leading-7 hidden">{!! $product['long_desc'] !!}</div>
            <button id="read-more-btn" type="button" class="mt-3 inline-flex items-center text-sm font-semibold text-green-700 hover:text-green-800">
              আরও দেখুন
            </button>
          @else
            <div class="text-gray-700 leading-7">{!! $product['long_desc'] !!}</div>
          @endif
        @endif
      </div>

      <div class="space-y-3">
        <button
          id="detail-order-btn"
          data-product-id="{{ $product['id'] }}"
          class="w-full bg-green-700 text-white py-3 rounded-xl hover:bg-green-800 font-semibold">
          এই পণ্যটি অর্ডার করুন
        </button>
        <p class="text-sm text-gray-500">অর্ডার বাটনে ক্লিক করলে পণ্যটি কার্টে যোগ হবে এবং এই পেজ থেকেই চেকআউট সম্পন্ন করতে পারবেন।</p>
      </div>
    </div>
  </div>

  @include('front-site.partials.cart-checkout')
</div>

@include('front-site.partials.visitor-modal')
@endsection

@include('front-site.partials.cart-checkout-script', ['isDetailPage' => true, 'focusedProductId' => $product['id']])

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const readMoreBtn = document.getElementById('read-more-btn');
    const longDescPreviewEl = document.getElementById('long-desc-preview');
    const longDescFullEl = document.getElementById('long-desc-full');

    if (readMoreBtn && longDescPreviewEl && longDescFullEl) {
        readMoreBtn.addEventListener('click', function () {
            const isHidden = longDescFullEl.classList.contains('hidden');

            longDescFullEl.classList.toggle('hidden');
            longDescPreviewEl.classList.toggle('hidden');
            readMoreBtn.textContent = isHidden ? 'কম দেখুন' : 'আরও দেখুন';
        });
    }
});
</script>
@endpush
