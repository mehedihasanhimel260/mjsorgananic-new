<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MJS-Organic Shop</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
