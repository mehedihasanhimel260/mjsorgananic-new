@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/80 px-6 py-5 sm:px-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-emerald-600">FAQ Setup</p>
                        <h1 class="mt-2 text-2xl font-bold text-slate-900">Add New FAQ</h1>
                        <p class="mt-2 text-sm text-slate-500">Question, answer, status, আর keywords দিয়ে reply-ready FAQ তৈরি করুন।</p>
                    </div>
                    <a href="{{ route('admin.faqs.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <span class="icon"><i class="mdi mdi-arrow-left"></i></span>
                        <span>Back to FAQs</span>
                    </a>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                <form method="POST" action="{{ route('admin.faqs.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Question</label>
                            <input class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-slate-800 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" type="text" name="question" value="{{ old('question') }}" placeholder="Write customer question" required>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                            <select name="status" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-slate-800 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" required>
                                @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(old('status', 'active') === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <p class="text-sm font-semibold text-slate-700">Status Guide</p>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                `Active` reply দেবে, `Pending` review-এর জন্য থাকবে, `Inactive` reply-তে যাবে না।
                            </p>
                        </div>

                        <div class="lg:col-span-2">
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Answer</label>
                            <textarea class="min-h-[180px] w-full rounded-2xl border border-slate-200 px-4 py-3 text-slate-800 shadow-sm outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" name="answer" placeholder="Write reply for customer" required>{{ old('answer') }}</textarea>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">Keywords</h2>
                                <p class="text-sm text-slate-500">Message-এর specific word match হলে এই FAQ reply হবে।</p>
                            </div>
                            <button type="button" id="add-keyword" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-500">
                                <span class="icon"><i class="mdi mdi-plus"></i></span>
                                <span>Add Keyword</span>
                            </button>
                        </div>

                        <div id="keyword-wrapper" class="mt-5 space-y-3">
                            @php
                                $keywords = old('keyword', ['']);
                            @endphp
                            @foreach ($keywords as $keyword)
                            <div class="keyword-row flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-3 sm:flex-row">
                                <input class="flex-1 rounded-xl border border-slate-200 px-4 py-3 text-slate-800 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" type="text" name="keyword[]" value="{{ $keyword }}" placeholder="Enter keyword">
                                <button type="button" class="remove-keyword inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 px-4 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                                    <span class="icon"><i class="mdi mdi-close"></i></span>
                                    <span>Remove</span>
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                        <a href="{{ route('admin.faqs.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-900/20 transition hover:bg-emerald-500">
                            <span class="icon"><i class="mdi mdi-content-save"></i></span>
                            <span>Save FAQ</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('keyword-wrapper');
    const addButton = document.getElementById('add-keyword');

    addButton.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'keyword-row flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-3 sm:flex-row';
        row.innerHTML = '<input class="flex-1 rounded-xl border border-slate-200 px-4 py-3 text-slate-800 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" type="text" name="keyword[]" placeholder="Enter keyword"><button type="button" class="remove-keyword inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 px-4 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-50"><span class="icon"><i class="mdi mdi-close"></i></span><span>Remove</span></button>';
        wrapper.appendChild(row);
    });

    wrapper.addEventListener('click', function (event) {
        const removeButton = event.target.closest('.remove-keyword');
        if (!removeButton) {
            return;
        }

        if (wrapper.querySelectorAll('.keyword-row').length === 1) {
            wrapper.querySelector('input').value = '';
            return;
        }

        removeButton.closest('.keyword-row').remove();
    });
});
</script>
@endpush
