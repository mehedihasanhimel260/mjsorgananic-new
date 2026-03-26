@extends('front-site.layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">
  <div class="bg-white rounded-2xl shadow overflow-hidden">
    @if(!empty($page->banner_image))
      <img src="{{ asset($page->banner_image) }}" alt="{{ $page->title }}" class="w-full h-72 object-cover">
    @endif
    <div class="p-6 lg:p-10">
      <h1 class="text-3xl font-bold text-gray-900">{{ $page->title }}</h1>
      @if(!empty($page->short_intro))
        <p class="mt-4 text-gray-600">{{ $page->short_intro }}</p>
      @endif
      <div class="prose max-w-none mt-8">
        {!! $page->content !!}
      </div>
    </div>
  </div>
</div>
@endsection
