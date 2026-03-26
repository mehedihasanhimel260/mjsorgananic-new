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
@include('front-site.partials.chat')
@include('front-site.partials.location')
<script src="{{ asset('assets/js/chat.js') }}"></script>
@stack("script")
</body>
</html>
