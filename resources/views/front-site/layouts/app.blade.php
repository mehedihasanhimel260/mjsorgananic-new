<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  @php
    $seoTitle = $seoSetting?->title ?: ($seoSetting?->site_name ?: 'MJS Organic');
    $seoSubtitle = $seoSetting?->subtitle ? ' - ' . $seoSetting->subtitle : '';
    $metaDescription = $seoSetting?->meta_description ?: $seoSetting?->subtitle;
    $appleTouchIcon = $seoSetting?->apple_touch_icon ? asset($seoSetting->apple_touch_icon) : asset('assets/apple-touch-icon.png');
    $favicon32 = $seoSetting?->favicon_32 ? asset($seoSetting->favicon_32) : asset('assets/favicon-32x32.png');
    $favicon16 = $seoSetting?->favicon_16 ? asset($seoSetting->favicon_16) : asset('assets/favicon-16x16.png');
    $maskIcon = $seoSetting?->mask_icon ? asset($seoSetting->mask_icon) : asset('assets/safari-pinned-tab.svg');
    $maskIconColor = $seoSetting?->mask_icon_color ?: '#00b4b6';
    $ogUrl = $seoSetting?->og_url ?: url()->current();
    $ogSiteName = $seoSetting?->og_site_name ?: ($seoSetting?->site_name ?: config('app.name'));
    $ogTitle = $seoSetting?->og_title ?: $seoTitle;
    $ogDescription = $seoSetting?->og_description ?: $metaDescription;
    $ogImage = $seoSetting?->og_image ?: $appleTouchIcon;
    $twitterCard = $seoSetting?->twitter_card ?: 'summary_large_image';
    $twitterTitle = $seoSetting?->twitter_title ?: $ogTitle;
    $twitterDescription = $seoSetting?->twitter_description ?: $ogDescription;
    $twitterImage = $seoSetting?->twitter_image ?: $ogImage;
  @endphp
  <title>{{ $seoTitle . $seoSubtitle }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ $appleTouchIcon }}"/>
  <link rel="icon" type="image/png" sizes="32x32" href="{{ $favicon32 }}"/>
  <link rel="icon" type="image/png" sizes="16x16" href="{{ $favicon16 }}"/>
  <link rel="mask-icon" href="{{ $maskIcon }}" color="{{ $maskIconColor }}"/>
  <meta name="description" content="{{ $metaDescription }}">
  @if(!empty($seoSetting?->meta_keywords))
  <meta name="keywords" content="{{ $seoSetting->meta_keywords }}">
  @endif
  <meta property="og:url" content="{{ $ogUrl }}">
  <meta property="og:site_name" content="{{ $ogSiteName }}">
  <meta property="og:title" content="{{ $ogTitle }}">
  <meta property="og:description" content="{{ $ogDescription }}">
  <meta property="og:image" content="{{ $ogImage }}">
  <meta property="twitter:card" content="{{ $twitterCard }}">
  <meta property="twitter:title" content="{{ $twitterTitle }}">
  <meta property="twitter:description" content="{{ $twitterDescription }}">
  <meta property="twitter:image:src" content="{{ $twitterImage }}">

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  @if(!empty($fbSetting?->pixel_id))
  <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ $fbSetting->pixel_id }}');
    fbq('track', 'PageView');
    @if(!empty($fbSetting->event_id))
    fbq('trackCustom', '{{ $fbSetting->event_id }}');
    @endif
  </script>
  <noscript>
    <img height="1" width="1" style="display:none"
      src="https://www.facebook.com/tr?id={{ $fbSetting->pixel_id }}&ev=PageView&noscript=1"/>
  </noscript>
  @endif
</head>

<body class="bg-gray-100">

<header class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 shadow-sm backdrop-blur" x-data="{ mobileMenuOpen: false }">
  <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
    <a href="{{ route('home') }}" class="inline-flex items-center px-5 py-3 bg-black rounded-xl shadow-lg">
      <span class="text-white text-2xl font-medium">MJS</span>
      <span class="text-green-400 text-2xl font-black ml-2">Organic</span>
    </a>

    <button
      type="button"
      class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 text-gray-700 shadow-sm transition hover:bg-gray-100"
      @click="mobileMenuOpen = !mobileMenuOpen"
      aria-label="Toggle menu">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16" />
      </svg>
    </button>
  </div>

  <div x-show="mobileMenuOpen" x-transition class="border-t border-gray-100 bg-white" style="display: none;">
    <nav class="max-w-6xl mx-auto px-4 py-4 flex flex-col gap-2 text-sm font-medium text-gray-700">
      <a href="{{ route('home') }}" class="rounded-xl px-4 py-3 hover:bg-green-50 hover:text-green-700">Home</a>
      <a href="{{ route('home') }}#product-list" class="rounded-xl px-4 py-3 hover:bg-green-50 hover:text-green-700">Products</a>
      <a href="#cart-section" class="rounded-xl px-4 py-3 hover:bg-green-50 hover:text-green-700" @click="mobileMenuOpen = false">Cart</a>
      <a href="#footer-contact" class="rounded-xl px-4 py-3 hover:bg-green-50 hover:text-green-700" @click="mobileMenuOpen = false">Contact Us</a>
      @if($siteSetting?->chat_active ?? true)
      <a href="#chat-widget" class="rounded-xl px-4 py-3 hover:bg-green-50 hover:text-green-700" @click="mobileMenuOpen = false">Chat</a>
      @endif
      @foreach($publicMenus as $menu)
        @php
          $menuUrl = '#';
          if ($menu->menu_type === 'internal_page' && $menu->target_slug) {
              $menuUrl = route('pages.show', $menu->target_slug);
          } elseif ($menu->menu_type === 'product_section') {
              $menuUrl = route('home').'#product-list';
          } elseif (!empty($menu->url)) {
              $menuUrl = $menu->url;
          }
        @endphp
        <a href="{{ $menuUrl }}" class="rounded-xl px-4 py-3 hover:bg-green-50 hover:text-green-700" {{ $menu->open_in_new_tab ? 'target=_blank rel=noopener noreferrer' : '' }}>
          {{ $menu->title }}
        </a>
      @endforeach
    </nav>
  </div>
</header>

<div class="max-w-6xl mx-auto px-4 pt-4">
  @if(session('success'))
    <div class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-green-700">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-700">
      {{ session('error') }}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-700">
      {{ $errors->first() }}
    </div>
  @endif
</div>

@yield('content')
<footer class="bg-white border-t border-gray-200 mt-16">
  <div class="max-w-6xl mx-auto px-4 py-10 grid gap-8 md:grid-cols-3">
    <div>
      @if(!empty($siteSetting?->footer_logo))
        <img src="{{ asset($siteSetting->footer_logo) }}" alt="Footer Logo" class="h-12 mb-4 rounded">
      @endif
      <h3 class="text-lg font-bold text-gray-900">{{ $siteSetting?->site_name ?: 'MJS Organic' }}</h3>
      @if(!empty($siteSetting?->footer_text))
        <p class="mt-3 text-sm text-gray-600">{{ $siteSetting->footer_text }}</p>
      @endif
    </div>
    <div>
      <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-900">{{ $siteSetting?->footer_quick_links_title ?: 'Quick Links' }}</h4>
      <div class="mt-4 flex flex-col gap-2 text-sm text-gray-600">
        @foreach($footerPages as $footerPage)
          <a href="{{ route('pages.show', $footerPage->slug) }}" class="hover:text-green-700">{{ $footerPage->title }}</a>
        @endforeach
      </div>
    </div>
    <div id="footer-contact">
      <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-900">Contact</h4>
      <div class="mt-4 space-y-2 text-sm text-gray-600">
        @if(!empty($siteSetting?->contact_phone))<p>Phone: {{ $siteSetting->contact_phone }}</p>@endif
        @if(!empty($siteSetting?->whatsapp_number))<p>WhatsApp: {{ $siteSetting->whatsapp_number }}</p>@endif
        @if(!empty($siteSetting?->support_email))<p>Email: {{ $siteSetting->support_email }}</p>@endif
        @if(!empty($siteSetting?->default_address))<p>{{ $siteSetting->default_address }}</p>@endif
      </div>
    </div>
  </div>
  <div class="border-t border-gray-100 py-4 text-center text-sm text-gray-500">
    {{ $siteSetting?->copyright_text ?: ('© '.now()->year.' MJS Organic') }}
  </div>
</footer>
@if($siteSetting?->chat_active ?? true)
@include('front-site.partials.chat')
@endif
@include('front-site.partials.location')
@if($siteSetting?->chat_active ?? true)
<script src="{{ asset('assets/js/chat.js') }}"></script>
@endif
@stack("script")
</body>
</html>
