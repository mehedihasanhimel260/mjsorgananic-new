@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-cog-outline"></i></span>
                General Settings
            </p>
        </header>
        <div class="card-content">
            <form method="POST" action="{{ route('admin.site-settings.general.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="field">
                        <label class="label">Site Name</label>
                        <input class="input" type="text" name="site_name" value="{{ old('site_name', $setting->site_name) }}">
                    </div>
                    <div class="field">
                        <label class="label">Site Tagline</label>
                        <input class="input" type="text" name="site_tagline" value="{{ old('site_tagline', $setting->site_tagline) }}">
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
                        <label class="label">Footer Quick Links Title</label>
                        <input class="input" type="text" name="footer_quick_links_title" value="{{ old('footer_quick_links_title', $setting->footer_quick_links_title) }}">
                    </div>
                    <div class="field">
                        <label class="label">Affiliate Minimum Withdraw Amount</label>
                        <input class="input" type="number" step="0.01" min="0" name="affiliate_minimum_withdraw_amount" value="{{ old('affiliate_minimum_withdraw_amount', $setting->affiliate_minimum_withdraw_amount ?? 500) }}">
                    </div>
                    <div class="field">
                        <label class="label">Affiliate Minimum Order Amount</label>
                        <input class="input" type="number" step="0.01" min="0" name="affiliate_minimum_order_amount" value="{{ old('affiliate_minimum_order_amount', $setting->affiliate_minimum_order_amount ?? 0) }}">
                    </div>
                    <div class="field md:col-span-2">
                        <label class="label">Default Address</label>
                        <textarea class="textarea" name="default_address" rows="3">{{ old('default_address', $setting->default_address) }}</textarea>
                    </div>
                    <div class="field md:col-span-2">
                        <label class="label">Footer Text</label>
                        <textarea class="textarea" name="footer_text" rows="4">{{ old('footer_text', $setting->footer_text) }}</textarea>
                    </div>
                    <div class="field md:col-span-2">
                        <label class="label">Copyright Text</label>
                        <input class="input" type="text" name="copyright_text" value="{{ old('copyright_text', $setting->copyright_text) }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div class="field"><label class="label">Facebook URL</label><input class="input" type="text" name="facebook_url" value="{{ old('facebook_url', $setting->facebook_url) }}"></div>
                    <div class="field"><label class="label">Instagram URL</label><input class="input" type="text" name="instagram_url" value="{{ old('instagram_url', $setting->instagram_url) }}"></div>
                    <div class="field"><label class="label">YouTube URL</label><input class="input" type="text" name="youtube_url" value="{{ old('youtube_url', $setting->youtube_url) }}"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div class="field"><label class="label">Logo</label><input class="input" type="file" name="logo"> @if($setting->logo)<img src="{{ asset($setting->logo) }}" class="mt-2 h-12">@endif</div>
                    <div class="field"><label class="label">Favicon</label><input class="input" type="file" name="favicon"> @if($setting->favicon)<img src="{{ asset($setting->favicon) }}" class="mt-2 h-12">@endif</div>
                    <div class="field"><label class="label">Footer Logo</label><input class="input" type="file" name="footer_logo"> @if($setting->footer_logo)<img src="{{ asset($setting->footer_logo) }}" class="mt-2 h-12">@endif</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <label class="checkbox"><input type="checkbox" name="site_active" value="1" {{ old('site_active', $setting->site_active) ? 'checked' : '' }}> <span class="check"></span><span class="control-label">Site Active</span></label>
                    <label class="checkbox"><input type="checkbox" name="chat_active" value="1" {{ old('chat_active', $setting->chat_active) ? 'checked' : '' }}> <span class="check"></span><span class="control-label">Chat Active</span></label>
                    <label class="checkbox"><input type="checkbox" name="affiliate_active" value="1" {{ old('affiliate_active', $setting->affiliate_active) ? 'checked' : '' }}> <span class="check"></span><span class="control-label">Affiliate Active</span></label>
                </div>

                <div class="field grouped mt-6">
                    <div class="control"><button type="submit" class="button green">Update Settings</button></div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
