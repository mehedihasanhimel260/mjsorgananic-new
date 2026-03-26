<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Affiliate Dashboard</title>

  <link rel="stylesheet" href="{{ asset('assets/css/main.css?v=1652870200386') }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
</head>
<body>
  <div id="app">
    @include('affiliates.partials.header')
    @include('affiliates.partials.aside')
    <div class="p-4">
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
    @include('affiliates.partials.footer')
  </div>

  <script type="text/javascript" src="{{ asset('assets/js/main.min.js?v=1652870200386') }}"></script>
  @stack('scripts')
</body>
</html>
