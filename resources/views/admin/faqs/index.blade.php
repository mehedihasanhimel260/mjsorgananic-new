@extends('layouts.admin_main')

@section('content')
@php
    $activeCount = $faqs->where('status', 'active')->count();
    $pendingCount = $faqs->where('status', 'pending')->count();
    $inactiveCount = $faqs->where('status', 'inactive')->count();
@endphp

<section class="section main-section">
    <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white shadow-xl">
            <div class="flex flex-col gap-6 px-6 py-7 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                <div class="max-w-3xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-300">Knowledge Base</p>
                    <h1 class="mt-3 text-2xl font-bold sm:text-3xl">FAQ Management</h1>
                    <p class="mt-2 text-sm text-slate-200 sm:text-base">
                        Active FAQ দিয়েই auto-reply যাবে। Pending FAQ review করে active করতে পারবেন, আর inactive item একসাথে বা single delete করা যাবে।
                    </p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap lg:justify-end">
                    <form method="POST" action="{{ route('admin.faqs.destroy-inactive') }}" onsubmit="return confirm('Delete all inactive FAQs?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-red-300/30 bg-red-500/20 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-500/30 sm:w-auto">
                            <span class="icon"><i class="mdi mdi-delete-sweep"></i></span>
                            <span>Delete Inactive</span>
                        </button>
                    </form>
                    <a href="{{ route('admin.faqs.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-900/30 transition hover:bg-emerald-400">
                        <span class="icon"><i class="mdi mdi-plus"></i></span>
                        <span>Add FAQ</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Total FAQ</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $faqs->count() }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-emerald-700">Active</p>
                <p class="mt-2 text-3xl font-bold text-emerald-800">{{ $activeCount }}</p>
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-amber-700">Pending</p>
                <p class="mt-2 text-3xl font-bold text-amber-800">{{ $pendingCount }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Inactive</p>
                <p class="mt-2 text-3xl font-bold text-slate-700">{{ $inactiveCount }}</p>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">All FAQs</h2>
                        <p class="text-sm text-slate-500">Mobile-friendly card view with quick action controls.</p>
                    </div>
                </div>
            </div>

            <div class="space-y-4 p-4 sm:p-6">
                @forelse ($faqs as $faq)
                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/70 shadow-sm transition hover:border-slate-300 hover:shadow-md">
                    <div class="flex flex-col gap-4 p-4 sm:p-5 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $faq->status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($faq->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-slate-200 text-slate-700') }}">
                                    {{ ucfirst($faq->status) }}
                                </span>
                                <span class="text-xs font-medium uppercase tracking-[0.2em] text-slate-400">FAQ #{{ $faq->id }}</span>
                            </div>

                            <h3 class="mt-3 text-base font-semibold leading-7 text-slate-900 sm:text-lg">{{ $faq->question }}</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">{{ $faq->answer }}</p>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl bg-white px-4 py-3">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Keywords</p>
                                    <p class="mt-2 text-sm text-slate-600">{{ $faq->keyword_list ? implode(', ', $faq->keyword_list) : 'No keywords added' }}</p>
                                </div>
                                <div class="rounded-2xl bg-white px-4 py-3">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Updated</p>
                                    <p class="mt-2 text-sm text-slate-600">{{ optional($faq->updated_at)->format('Y-m-d h:i A') ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex w-full shrink-0 flex-row gap-2 sm:w-auto sm:flex-col">
                            <a href="{{ route('admin.faqs.edit', $faq->id) }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-500 sm:flex-none">
                                <span class="icon"><i class="mdi mdi-pencil"></i></span>
                                <span>Edit</span>
                            </a>
                            <form method="POST" action="{{ route('admin.faqs.destroy', $faq->id) }}" class="flex-1 sm:flex-none" onsubmit="return confirm('Delete this FAQ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-red-200 bg-white px-4 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                                    <span class="icon"><i class="mdi mdi-trash-can-outline"></i></span>
                                    <span>Delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
                @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-16 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white shadow-sm">
                        <i class="mdi mdi-frequently-asked-questions text-3xl text-slate-400"></i>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-slate-800">No FAQ found</h3>
                    <p class="mt-2 text-sm text-slate-500">Create your first FAQ to start auto-replying from the knowledge base.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
