@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-frequently-asked-questions"></i></span>
                FAQ List
            </p>
            <a href="{{ route('admin.faqs.create') }}" class="button blue">
                <span class="icon"><i class="mdi mdi-plus"></i></span>
                <span>Add Question</span>
            </a>
        </header>
        <div class="card-content space-y-4">
            @forelse ($faqs as $faq)
            <details class="border rounded-lg bg-white shadow-sm">
                <summary class="cursor-pointer px-4 py-3 font-semibold flex items-center justify-between">
                    <span>{{ $faq->question }}</span>
                    <a href="{{ route('admin.faqs.edit', $faq->id) }}" class="button small blue" onclick="event.stopPropagation();">
                        <span class="icon"><i class="mdi mdi-pencil"></i></span>
                    </a>
                </summary>
                <div class="px-4 pb-4">
                    <p class="mb-2 text-gray-700">{{ $faq->answer }}</p>
                    <p class="text-sm text-gray-500"><strong>Keyword:</strong> {{ $faq->keyword ?? 'N/A' }}</p>
                </div>
            </details>
            @empty
            <div class="text-center text-gray-500">No FAQ found.</div>
            @endforelse
        </div>
    </div>
</section>
@endsection
