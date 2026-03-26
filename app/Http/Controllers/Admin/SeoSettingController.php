<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SeoSettingController extends Controller
{
    private function getSetting(): SeoSetting
    {
        return SeoSetting::firstOrCreate([], [
            'site_name' => config('app.name', 'MJS Organic'),
            'title' => config('app.name', 'MJS Organic'),
            'subtitle' => 'Organic Shop',
            'meta_description' => null,
            'meta_keywords' => null,
            'og_url' => url('/'),
            'og_site_name' => config('app.name', 'MJS Organic'),
            'og_title' => config('app.name', 'MJS Organic'),
            'og_description' => null,
            'og_image' => 'assets/apple-touch-icon.png',
            'twitter_card' => 'summary_large_image',
            'twitter_title' => config('app.name', 'MJS Organic'),
            'twitter_description' => null,
            'twitter_image' => 'assets/apple-touch-icon.png',
            'apple_touch_icon' => 'assets/apple-touch-icon.png',
            'favicon_32' => 'assets/favicon-32x32.png',
            'favicon_16' => 'assets/favicon-16x16.png',
            'mask_icon' => 'assets/safari-pinned-tab.svg',
            'mask_icon_color' => '#00b4b6',
        ]);
    }

    public function index()
    {
        $setting = $this->getSetting();

        return view('admin.seo-settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'meta_keywords' => 'nullable|string|max:1000',
            'og_url' => 'nullable|string|max:2048',
            'og_site_name' => 'nullable|string|max:255',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:1000',
            'og_image' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:4096',
            'twitter_card' => 'nullable|string|max:100',
            'twitter_title' => 'nullable|string|max:255',
            'twitter_description' => 'nullable|string|max:1000',
            'twitter_image' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:4096',
            'apple_touch_icon' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'favicon_32' => 'nullable|image|mimes:jpg,jpeg,png,webp,ico|max:2048',
            'favicon_16' => 'nullable|image|mimes:jpg,jpeg,png,webp,ico|max:2048',
            'mask_icon' => 'nullable|file|mimes:svg|max:2048',
            'mask_icon_color' => 'nullable|string|max:50',
        ]);

        $setting = $this->getSetting();

        foreach (['og_image', 'twitter_image', 'apple_touch_icon', 'favicon_32', 'favicon_16', 'mask_icon'] as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images'), $filename);
                $validated[$field] = 'images/' . $filename;
            } else {
                unset($validated[$field]);
            }
        }

        $setting->update($validated);

        return redirect()->route('admin.seo-settings.index')->with('success', 'SEO settings updated successfully.');
    }
}
