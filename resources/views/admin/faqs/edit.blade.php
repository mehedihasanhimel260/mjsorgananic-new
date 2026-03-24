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
                    <label class="label">Keyword</label>
                    <div class="control">
                        <input class="input" type="text" name="keyword" value="{{ old('keyword', $faq->keyword) }}">
                    </div>
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
