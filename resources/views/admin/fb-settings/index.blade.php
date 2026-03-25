@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-facebook"></i></span>
                Facebook Settings
            </p>
        </header>
        <div class="card-content">
            <form method="POST" action="{{ route('admin.fb-settings.update') }}">
                @csrf
                <div class="field">
                    <label class="label">FB Page ID</label>
                    <div class="control">
                        <input class="input" type="text" name="fb_page_id" value="{{ old('fb_page_id', $setting->fb_page_id) }}" placeholder="Enter FB Page ID">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Access Token</label>
                    <div class="control">
                        <textarea class="textarea" name="access_token" rows="4" placeholder="Enter Access Token">{{ old('access_token', $setting->access_token) }}</textarea>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Pixel ID</label>
                    <div class="control">
                        <input class="input" type="text" name="pixel_id" value="{{ old('pixel_id', $setting->pixel_id) }}" placeholder="Enter Pixel ID">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Event ID</label>
                    <div class="control">
                        <input class="input" type="text" name="event_id" value="{{ old('event_id', $setting->event_id) }}" placeholder="Enter Event ID">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Verify Token</label>
                    <div class="control">
                        <input class="input" type="text" name="verify_token" value="{{ old('verify_token', $setting->verify_token) }}" placeholder="Enter Verify Token">
                    </div>
                </div>

                <div class="field grouped">
                    <div class="control">
                        <button type="submit" class="button green">Update Settings</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
