<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MJS-Organic Shop</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

@yield('content')
@include('front-site.partials.location')
@stack("script")

</body>
</html>
