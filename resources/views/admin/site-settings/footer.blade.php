@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-page-layout-footer"></i></span>
                Footer Settings
            </p>
        </header>
        <div class="card-content">
            <form method="POST" action="{{ route('admin.site-settings.footer.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="field">
                        <label class="label">Footer Logo</label>
                        <input class="input" type="file" name="footer_logo">
                        @if($setting->footer_logo)
                            <img src="{{ asset($setting->footer_logo) }}" class="mt-2 h-12 rounded">
                        @endif
                    </div>
                    <div class="field">
                        <label class="label">Footer Quick Links Title</label>
                        <input class="input" type="text" name="footer_quick_links_title" value="{{ old('footer_quick_links_title', $setting->footer_quick_links_title) }}">
                    </div>
                    <div class="field md:col-span-2">
                        <label class="label">Footer Text</label>
                        <textarea class="textarea" name="footer_text" rows="4">{{ old('footer_text', $setting->footer_text) }}</textarea>
                    </div>
                    <div class="field md:col-span-2">
                        <label class="label">Copyright Text</label>
                        <input class="input" type="text" name="copyright_text" value="{{ old('copyright_text', $setting->copyright_text) }}">
                    </div>
                    <div class="field">
                        <label class="label">Contact Phone</label>
                        <input class="input" type="text" name="contact_phone" value="{{ old('contact_phone', $setting->contact_phone) }}">
                    </div>
                    <div class="field">
                        <label class="label">WhatsApp Number</label>
                        <input class="input" type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $setting->whatsapp_number) }}">
                    </div>
                    <div class="field">
                        <label class="label">Support Email</label>
                        <input class="input" type="email" name="support_email" value="{{ old('support_email', $setting->support_email) }}">
                    </div>
                    <div class="field">
                        <label class="label">Default Address</label>
                        <textarea class="textarea" name="default_address" rows="3">{{ old('default_address', $setting->default_address) }}</textarea>
                    </div>
                </div>
                <div class="field grouped mt-6">
                    <div class="control"><button type="submit" class="button green">Update Footer</button></div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
