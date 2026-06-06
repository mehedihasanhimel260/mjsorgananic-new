@extends('front-site.layouts.app')

@section('content')
@php
  $productImage = $product->image ? asset('images/'.$product->image) : asset('assets/img/justboil-logo.svg');
  $heroImage = $landingPage->hero_image ? asset('images/'.$landingPage->hero_image) : $productImage;
  $price = (float) $product->selling_price;
  $landingProducts = $landingPage->products;
  $landingProductOptions = $landingProducts->map(function ($item) {
      return [
          'id' => $item->id,
          'name' => $item->name,
          'price' => (float) $item->selling_price,
          'image' => $item->image ? asset('images/'.$item->image) : asset('assets/img/justboil-logo.svg'),
      ];
  })->values();
  $ingredients = $landingPage->ingredients ?: [];
  $benefits = $landingPage->benefits ?: [];
  $steps = $landingPage->how_to_use ?: [];
  $reviews = $landingPage->customer_reviews ?: [];
  $galleryImages = $landingPage->gallery_images ?: [];
@endphp

<main class="bg-[#fff8ec] text-[#213b2a]">
  <section class="relative overflow-hidden">
    <div class="absolute left-0 top-0 h-72 w-72 rounded-full bg-[#d89b27]/20 blur-3xl"></div>
    <div class="absolute right-0 top-24 h-96 w-96 rounded-full bg-[#557a46]/20 blur-3xl"></div>
    <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 py-14 lg:grid-cols-[1.05fr,0.95fr] lg:py-20">
      <div class="relative z-10">
        <h1 class="mt-6 text-4xl font-black leading-tight text-[#213b2a] md:text-6xl">
          {{ $landingPage->hero_title ?: $landingPage->title }}
        </h1>
        @if($landingPage->hero_subtitle)
          <p class="mt-4 text-2xl font-bold text-[#557a46]">{{ $landingPage->hero_subtitle }}</p>
        @endif
        @if($landingPage->hero_description)
          <p class="mt-5 max-w-2xl text-lg leading-8 text-[#4d604f]">{{ $landingPage->hero_description }}</p>
        @endif
        <div class="mt-8 flex flex-wrap items-center gap-4">
          <a href="#landing-order-form" class="rounded-2xl bg-[#056608] px-7 py-4 text-base font-black text-white shadow-lg transition hover:bg-[#034d06]">
            Order Now
          </a>
          <div class="rounded-2xl bg-white px-6 py-4 text-2xl font-black text-[#056608] shadow-sm">
            &#2547; {{ number_format($price, 2) }}
          </div>
        </div>
      </div>

      <div class="relative z-10">
        <div class="rounded-[2rem] border border-white/80 bg-white/80 p-5 shadow-2xl backdrop-blur">
          <img src="{{ $heroImage }}" alt="{{ $landingPage->title }}" class="h-[420px] w-full rounded-[1.5rem] object-cover">
        </div>
      </div>
    </div>
  </section>

  <section class="mx-auto max-w-6xl px-4 py-8">
    <div class="grid gap-5 md:grid-cols-2">
      @foreach($landingProducts as $landingProduct)
        @php($landingProductImage = $landingProduct->image ? asset('images/'.$landingProduct->image) : asset('assets/img/justboil-logo.svg'))
        <div class="grid gap-5 rounded-[2rem] bg-white p-5 shadow-xl sm:grid-cols-[150px,1fr]">
          <img src="{{ $landingProductImage }}" alt="{{ $landingProduct->name }}" class="h-44 w-full rounded-3xl object-cover">
          <div>
            <h2 class="text-2xl font-black">{{ $landingProduct->name }}</h2>
            @if($landingProduct->short_description)
              <p class="mt-3 line-clamp-3 text-sm leading-6 text-gray-600">{{ $landingProduct->short_description }}</p>
            @endif
            <p class="mt-4 text-3xl font-black text-[#056608]">&#2547; {{ number_format((float) $landingProduct->selling_price, 2) }}</p>
            <a href="#landing-order-form" class="mt-4 inline-flex rounded-2xl bg-[#d89b27] px-5 py-3 font-black text-white transition hover:bg-[#b77b14]">
              Add to Order
            </a>
          </div>
        </div>
      @endforeach
    </div>
  </section>

  @if(count($ingredients))
    <section class="mx-auto max-w-6xl px-4 py-12">
      <div class="text-center">
        <p class="text-sm font-black uppercase tracking-[0.3em] text-[#557a46]">Ingredients</p>
        <h2 class="mt-3 text-4xl font-black">{{ $landingPage->ingredients_title ?: 'Ingredients Benefits' }}</h2>
      </div>
      <div class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        @foreach($ingredients as $ingredient)
          <div class="rounded-3xl border border-[#e8d9bc] bg-white p-6 shadow-sm">
            @if(!empty($ingredient['image']))
              <img src="{{ asset('images/'.$ingredient['image']) }}" alt="{{ $ingredient['name'] ?? '' }}" class="mb-4 h-20 w-20 rounded-2xl object-cover">
            @else
              <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#f4fbf1] text-2xl font-black text-[#056608]">
                {{ mb_substr($ingredient['name'] ?? '✓', 0, 1) }}
              </div>
            @endif
            <h3 class="text-xl font-black">{{ $ingredient['name'] ?? '' }}</h3>
            <p class="mt-3 leading-7 text-gray-600">{{ $ingredient['description'] ?? '' }}</p>
          </div>
        @endforeach
      </div>
    </section>
  @endif

  @if(count($benefits))
    <section class="bg-[#f4fbf1] py-14">
      <div class="mx-auto max-w-6xl px-4">
        <h2 class="text-center text-4xl font-black">{{ $landingPage->benefits_title ?: 'Product Benefits' }}</h2>
        <div class="mt-8 grid gap-4 md:grid-cols-2">
          @foreach($benefits as $benefit)
            <div class="flex gap-4 rounded-3xl bg-white p-5 shadow-sm">
              <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#056608] font-black text-white">✓</span>
              <p class="text-lg font-semibold leading-8 text-[#2f4635]">{{ $benefit }}</p>
            </div>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  @if(count($galleryImages))
    <section class="mx-auto max-w-6xl px-4 py-14">
      <div class="grid gap-5 md:grid-cols-4">
        @foreach($galleryImages as $image)
          <img src="{{ asset('images/'.$image) }}" alt="{{ $landingPage->title }}" class="h-80 w-full rounded-[2rem] object-cover shadow-lg">
        @endforeach
      </div>
    </section>
  @endif

  @if(count($steps))
    <section class="mx-auto max-w-5xl px-4 py-12">
      <div class="rounded-[2rem] bg-[#213b2a] p-7 text-white shadow-xl md:p-10">
        <h2 class="text-4xl font-black">{{ $landingPage->how_to_use_title ?: 'How To Use' }}</h2>
        <div class="mt-7 grid gap-4">
          @foreach($steps as $step)
            <div class="flex gap-4 rounded-2xl bg-white/10 p-4">
              <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#d89b27] font-black">{{ $loop->iteration }}</span>
              <p class="leading-7">{{ $step }}</p>
            </div>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  @if(count($reviews))
    <section class="mx-auto max-w-6xl px-4 py-12">
      <h2 class="text-center text-4xl font-black">{{ $landingPage->reviews_title ?: 'Customer Review' }}</h2>
      <div class="mt-8 grid gap-5 md:grid-cols-3">
        @foreach($reviews as $review)
          <div class="rounded-3xl bg-white p-6 shadow-sm">
            <div class="text-[#d89b27]">{{ str_repeat('★', (int) ($review['rating'] ?? 5)) }}</div>
            <p class="mt-4 leading-7 text-gray-600">"{{ $review['text'] ?? '' }}"</p>
            <p class="mt-5 font-black text-[#213b2a]">{{ $review['name'] ?? 'Customer' }}</p>
          </div>
        @endforeach
      </div>
    </section>
  @endif

  <section id="landing-order-form" class="mx-auto max-w-6xl px-4 py-14">
    <div class="overflow-hidden rounded-[1.6rem] border-2 border-[#056608] bg-[#f8fff6] shadow-2xl">
      <div class="bg-[#056608] px-5 py-4 text-center text-2xl font-black text-white md:text-3xl">
        {{ $landingPage->checkout_title ?: 'Fill the form below and click Place Order.' }}
      </div>

      <form
        method="POST"
        action="{{ route('landing.order', $landingPage->id) }}"
        class="p-5 md:p-8"
        x-data="{
          qty: {{ max(1, (int) old('quantity', 1)) }},
          products: @js($landingProductOptions),
          selectedId: {{ (int) old('product_id', $product->id) }},
          get selectedProduct() {
            return this.products.find((item) => item.id === Number(this.selectedId)) || this.products[0];
          },
          get total() {
            return (this.qty * Number(this.selectedProduct.price || 0)).toFixed(2);
          }
        }">
        @csrf
        <input type="hidden" name="product_id" :value="selectedProduct.id">
        <input type="hidden" name="quantity" :value="qty">

        <h3 class="mb-4 text-lg font-black text-gray-500">Your Products</h3>
        <div class="grid gap-4 md:grid-cols-2">
          @foreach($landingProducts as $landingProduct)
            @php($landingProductImage = $landingProduct->image ? asset('images/'.$landingProduct->image) : asset('assets/img/justboil-logo.svg'))
            <label
              class="flex cursor-pointer gap-4 rounded-2xl border bg-white p-4 transition hover:border-[#056608]"
              :class="Number(selectedId) === {{ $landingProduct->id }} ? 'border-[#056608] ring-2 ring-[#056608]/20' : 'border-gray-200'">
              <input type="radio" class="mt-8" value="{{ $landingProduct->id }}" x-model.number="selectedId">
              <img src="{{ $landingProductImage }}" alt="{{ $landingProduct->name }}" class="h-20 w-20 rounded-xl object-cover">
              <div>
                <p class="font-black">{{ $landingProduct->name }}</p>
                <p class="text-sm text-gray-500">&#2547; {{ number_format((float) $landingProduct->selling_price, 2) }}</p>
              </div>
            </label>
          @endforeach
        </div>

        <div class="mt-5 rounded-2xl border bg-white p-5">
          <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-4">
              <img :src="selectedProduct.image" :alt="selectedProduct.name" class="h-20 w-20 rounded-xl object-cover">
              <div>
                <p class="font-black" x-text="selectedProduct.name"></p>
                <p class="text-sm text-gray-500">&#2547; <span x-text="Number(selectedProduct.price).toFixed(2)"></span> × <span x-text="qty"></span></p>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <div class="inline-flex overflow-hidden rounded-lg border bg-white">
                <button type="button" class="px-4 py-2 text-xl" @click="qty = Math.max(1, qty - 1)">-</button>
                <span class="min-w-12 px-4 py-2 text-center font-black" x-text="qty"></span>
                <button type="button" class="px-4 py-2 text-xl" @click="qty++">+</button>
              </div>
              <p class="text-xl font-black text-[#056608]">&#2547; <span x-text="total"></span></p>
            </div>
          </div>
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-[1.05fr,0.95fr]">
          <div>
            <h3 class="text-xl font-black">Billing details</h3>
            <div class="mt-6 space-y-5">
              <label class="block">
                <span class="font-semibold">Name *</span>
                <input type="text" name="name" value="{{ old('name', session('customer_name')) }}" required class="mt-2 w-full rounded-xl border border-dashed border-gray-500 bg-white px-4 py-3 focus:border-[#056608] focus:outline-none">
              </label>
              <label class="block">
                <span class="font-semibold">Phone *</span>
                <input type="text" name="phone" value="{{ old('phone', session('customer_phone')) }}" required class="mt-2 w-full rounded-xl border border-dashed border-gray-500 bg-white px-4 py-3 focus:border-[#056608] focus:outline-none">
              </label>
              <label class="block">
                <span class="font-semibold">Address *</span>
                <textarea name="address" rows="4" required class="mt-2 w-full rounded-xl border border-dashed border-gray-500 bg-white px-4 py-3 focus:border-[#056608] focus:outline-none">{{ old('address') }}</textarea>
              </label>
            </div>
          </div>

          <div>
            <h3 class="text-xl font-black">Your order</h3>
            <div class="mt-6 rounded-2xl bg-white p-5 shadow-sm">
              <div class="flex justify-between border-b pb-3 text-sm font-black">
                <span>Product</span>
                <span>Subtotal</span>
              </div>
              <div class="flex items-center justify-between gap-4 border-b py-4">
                <div class="flex items-center gap-3">
                  <img :src="selectedProduct.image" :alt="selectedProduct.name" class="h-14 w-14 rounded-lg object-cover">
                  <p class="text-sm font-semibold"><span x-text="selectedProduct.name"></span> × <span x-text="qty"></span></p>
                </div>
                <p class="font-bold">&#2547; <span x-text="total"></span></p>
              </div>
              <div class="flex justify-between border-b py-3">
                <span>Subtotal</span>
                <span>&#2547; <span x-text="total"></span></span>
              </div>
              <div class="flex justify-between py-3 text-lg font-black">
                <span>Total</span>
                <span>&#2547; <span x-text="total"></span></span>
              </div>
              <div class="mt-4 rounded-xl bg-[#f4fbf1] p-4 text-sm text-gray-600">Cash on delivery. Pay after receiving the product.</div>
              <button type="submit" class="mt-6 w-full rounded-xl bg-[#86b184] px-5 py-4 text-lg font-black text-white transition hover:bg-[#056608]">
                Place Order &#2547; <span x-text="total"></span>
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </section>

  @if($landingPage->final_cta_title || $landingPage->final_cta_text)
    <section class="mx-auto max-w-5xl px-4 pb-16 text-center">
      <div class="rounded-[2rem] bg-white p-8 shadow-xl">
        @if($landingPage->final_cta_title)
          <h2 class="text-4xl font-black">{{ $landingPage->final_cta_title }}</h2>
        @endif
        @if($landingPage->final_cta_text)
          <p class="mx-auto mt-4 max-w-3xl text-lg leading-8 text-gray-600">{{ $landingPage->final_cta_text }}</p>
        @endif
        <a href="#landing-order-form" class="mt-7 inline-flex rounded-2xl bg-[#056608] px-8 py-4 font-black text-white transition hover:bg-[#034d06]">Order Now</a>
      </div>
    </section>
  @endif
</main>
@endsection
