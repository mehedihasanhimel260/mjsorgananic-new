@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-pencil"></i></span>
                Edit FAQ
            </p>
        </header>
        <div class="card-content">
            <form method="POST" action="{{ route('admin.faqs.update', $faq->id) }}">
                @csrf
                @method('PATCH')
                <div class="field">
                    <label class="label">Question</label>
                    <div class="control">
                        <input class="input" type="text" name="question" value="{{ old('question', $faq->question) }}" required>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Answer</label>
                    <div class="control">
                        <textarea class="textarea" name="answer" required>{{ old('answer', $faq->answer) }}</textarea>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Keywords</label>
                    <div id="keyword-wrapper" class="space-y-3">
                        @php
                            $keywords = old('keyword', $faq->keyword_list ?: ['']);
                        @endphp
                        @foreach ($keywords as $keyword)
                        <div class="flex gap-2 keyword-row">
                            <input class="input" type="text" name="keyword[]" value="{{ $keyword }}" placeholder="Enter keyword">
                            <button type="button" class="button red remove-keyword">Remove</button>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" id="add-keyword" class="button blue mt-3">Add Keyword</button>
                </div>
                <hr>
                <div class="field grouped">
                    <div class="control">
                        <button type="submit" class="button green">Update</button>
                    </div>
                    <div class="control">
                        <a href="{{ route('admin.faqs.index') }}" class="button red">Cancel</a>
                    </div>
                </div>
            </form>
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
        row.className = 'flex gap-2 keyword-row';
        row.innerHTML = '<input class="input" type="text" name="keyword[]" placeholder="Enter keyword"><button type="button" class="button red remove-keyword">Remove</button>';
        wrapper.appendChild(row);
    });

    wrapper.addEventListener('click', function (event) {
        if (!event.target.classList.contains('remove-keyword')) {
            return;
        }

        if (wrapper.querySelectorAll('.keyword-row').length === 1) {
            wrapper.querySelector('input').value = '';
            return;
        }

        event.target.closest('.keyword-row').remove();
    });
});
</script>
@endpush
