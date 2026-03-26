@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-tag-multiple-outline"></i></span>
                SEO Settings
            </p>
        </header>
        <div class="card-content">
            <form method="POST" action="{{ route('admin.seo-settings.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">Site Name</label>
                            <div class="control">
                                <input class="input" type="text" name="site_name" value="{{ old('site_name', $setting->site_name) }}" placeholder="Enter site name">
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Subtitle</label>
                            <div class="control">
                                <input class="input" type="text" name="subtitle" value="{{ old('subtitle', $setting->subtitle) }}" placeholder="Enter subtitle">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Title</label>
                    <div class="control">
                        <input class="input" type="text" name="title" value="{{ old('title', $setting->title) }}" placeholder="Enter title">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Meta Description</label>
                    <div class="control">
                        <textarea class="textarea" name="meta_description" rows="3" placeholder="Enter meta description">{{ old('meta_description', $setting->meta_description) }}</textarea>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Meta Keywords</label>
                    <div class="control">
                        <textarea class="textarea" name="meta_keywords" rows="2" placeholder="keyword1, keyword2, keyword3">{{ old('meta_keywords', $setting->meta_keywords) }}</textarea>
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">Apple Touch Icon</label>
                            <div class="control">
                                <input class="input" type="file" name="apple_touch_icon" accept=".jpg,.jpeg,.png,.webp">
                            </div>
                            @if($setting->apple_touch_icon)
                                <p class="help">Current file: {{ $setting->apple_touch_icon }}</p>
                                <img src="{{ asset($setting->apple_touch_icon) }}" alt="Apple touch icon" class="mt-2 h-12 w-12 rounded border object-cover">
                            @endif
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Favicon 32x32</label>
                            <div class="control">
                                <input class="input" type="file" name="favicon_32" accept=".jpg,.jpeg,.png,.webp,.ico">
                            </div>
                            @if($setting->favicon_32)
                                <p class="help">Current file: {{ $setting->favicon_32 }}</p>
                                <img src="{{ asset($setting->favicon_32) }}" alt="Favicon 32x32" class="mt-2 h-10 w-10 rounded border object-cover">
                            @endif
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Favicon 16x16</label>
                            <div class="control">
                                <input class="input" type="file" name="favicon_16" accept=".jpg,.jpeg,.png,.webp,.ico">
                            </div>
                            @if($setting->favicon_16)
                                <p class="help">Current file: {{ $setting->favicon_16 }}</p>
                                <img src="{{ asset($setting->favicon_16) }}" alt="Favicon 16x16" class="mt-2 h-8 w-8 rounded border object-cover">
                            @endif
                        </div>
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">Mask Icon</label>
                            <div class="control">
                                <input class="input" type="file" name="mask_icon" accept=".svg">
                            </div>
                            @if($setting->mask_icon)
                                <p class="help">Current file: {{ $setting->mask_icon }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Mask Icon Color</label>
                            <div class="control">
                                <input class="input" type="text" name="mask_icon_color" value="{{ old('mask_icon_color', $setting->mask_icon_color) }}" placeholder="#00b4b6">
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-5">

                <div class="field">
                    <label class="label">Open Graph URL</label>
                    <div class="control">
                        <input class="input" type="text" name="og_url" value="{{ old('og_url', $setting->og_url) }}" placeholder="https://example.com">
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">OG Site Name</label>
                            <div class="control">
                                <input class="input" type="text" name="og_site_name" value="{{ old('og_site_name', $setting->og_site_name) }}" placeholder="Enter OG site name">
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">OG Title</label>
                            <div class="control">
                                <input class="input" type="text" name="og_title" value="{{ old('og_title', $setting->og_title) }}" placeholder="Enter OG title">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">OG Description</label>
                    <div class="control">
                        <textarea class="textarea" name="og_description" rows="3" placeholder="Enter OG description">{{ old('og_description', $setting->og_description) }}</textarea>
                    </div>
                </div>

                <div class="field">
                    <label class="label">OG Image</label>
                    <div class="control">
                        <input class="input" type="file" name="og_image" accept=".jpg,.jpeg,.png,.webp,.svg">
                    </div>
                    @if($setting->og_image)
                        <p class="help">Current file: {{ $setting->og_image }}</p>
                        <img src="{{ asset($setting->og_image) }}" alt="OG image" class="mt-2 h-24 w-24 rounded border object-cover">
                    @endif
                </div>

                <hr class="my-5">

                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">Twitter Card</label>
                            <div class="control">
                                <input class="input" type="text" name="twitter_card" value="{{ old('twitter_card', $setting->twitter_card) }}" placeholder="summary_large_image">
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Twitter Title</label>
                            <div class="control">
                                <input class="input" type="text" name="twitter_title" value="{{ old('twitter_title', $setting->twitter_title) }}" placeholder="Enter Twitter title">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Twitter Description</label>
                    <div class="control">
                        <textarea class="textarea" name="twitter_description" rows="3" placeholder="Enter Twitter description">{{ old('twitter_description', $setting->twitter_description) }}</textarea>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Twitter Image</label>
                    <div class="control">
                        <input class="input" type="file" name="twitter_image" accept=".jpg,.jpeg,.png,.webp,.svg">
                    </div>
                    @if($setting->twitter_image)
                        <p class="help">Current file: {{ $setting->twitter_image }}</p>
                        <img src="{{ asset($setting->twitter_image) }}" alt="Twitter image" class="mt-2 h-24 w-24 rounded border object-cover">
                    @endif
                </div>

                <div class="field grouped">
                    <div class="control">
                        <button type="submit" class="button green">Update SEO Settings</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
