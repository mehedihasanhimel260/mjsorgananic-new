@extends('layouts.admin_main')

@section('content')
@php
    $currentApiKey = old('api_key');
    $savedApiKey = $aiSetting->api_key;
    $maskedApiKey = 'No API key saved';

    if ($currentApiKey !== null && $currentApiKey !== '') {
        $maskedApiKey = strlen($currentApiKey) > 14
            ? substr($currentApiKey, 0, 8) . str_repeat('*', max(strlen($currentApiKey) - 14, 6)) . substr($currentApiKey, -6)
            : $currentApiKey;
    } elseif ($savedApiKey) {
        $maskedApiKey = strlen($savedApiKey) > 14
            ? substr($savedApiKey, 0, 8) . str_repeat('*', max(strlen($savedApiKey) - 14, 6)) . substr($savedApiKey, -6)
            : $savedApiKey;
    }
@endphp
<section class="section main-section">
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-pencil-circle-outline"></i></span>
                Edit AI Setting
            </p>
            <a href="{{ route('admin.ai-settings.index') }}" class="button blue">
                <span class="icon"><i class="mdi mdi-arrow-left"></i></span>
                <span>Back</span>
            </a>
        </header>
        <div class="card-content">
            <form method="POST" action="{{ route('admin.ai-settings.update', $aiSetting->id) }}">
                @csrf
                @method('PATCH')

                <div class="field">
                    <label class="label">Title</label>
                    <div class="control">
                        <input class="input" type="text" value="{{ $aiSetting->title }}" readonly>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Saved API-key</label>
                    <div class="control">
                        <input class="input" type="text" value="{{ $maskedApiKey }}" readonly>
                    </div>
                </div>

                <div class="field">
                    <label class="label">New API-key</label>
                    <div class="control">
                        <textarea class="textarea" name="api_key" rows="4" placeholder="New API key dile update hobe, khali rakhle old key thakbe">{{ old('api_key') }}</textarea>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Model Name</label>
                    <div class="control">
                        <input class="input" type="text" name="model_name" value="{{ old('model_name', $aiSetting->model_name) }}" placeholder="Enter model name">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Status</label>
                    <div class="control">
                        <label class="inline-flex cursor-pointer items-center gap-3">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="peer sr-only" {{ old('is_active', $aiSetting->is_active) ? 'checked' : '' }}>
                            <span class="relative h-6 w-11 rounded-full bg-gray-300 transition peer-checked:bg-green-500 after:absolute after:left-1 after:top-1 after:h-4 after:w-4 after:rounded-full after:bg-white after:transition-all peer-checked:after:left-6"></span>
                            <span class="text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                    <p class="help">Ei row On korle onno sob row automatically Off hoye jabe.</p>
                </div>

                <div class="field grouped">
                    <div class="control">
                        <button type="submit" class="button green">Update Setting</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
