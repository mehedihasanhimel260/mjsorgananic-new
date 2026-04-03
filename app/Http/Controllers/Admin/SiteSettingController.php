<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SiteSettingController extends Controller
{
    private function getSetting(): SiteSetting
    {
        return SiteSetting::firstOrCreate([], [
            'site_name' => 'MJS Organic',
            'site_tagline' => 'Organic Shop',
            'site_active' => true,
            'chat_active' => true,
            'affiliate_active' => true,
            'affiliate_minimum_withdraw_amount' => 500,
            'affiliate_minimum_order_amount' => 0,
            'footer_quick_links_title' => 'Quick Links',
        ]);
    }

    public function index()
    {
        $setting = $this->getSetting();

        return view('admin.site-settings.general', compact('setting'));
    }

    public function update(Request $request)
    {
        return $this->saveAndRedirect($request, 'admin.site-settings.general', 'Site settings updated successfully.');
    }

    public function footer()
    {
        $setting = $this->getSetting();

        return view('admin.site-settings.footer', compact('setting'));
    }

    public function updateFooter(Request $request)
    {
        return $this->saveAndRedirect($request, 'admin.site-settings.footer', 'Footer settings updated successfully.');
    }

    private function saveAndRedirect(Request $request, string $routeName, string $message)
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:50',
            'support_email' => 'nullable|email|max:255',
            'default_address' => 'nullable|string|max:2000',
            'facebook_url' => 'nullable|string|max:2048',
            'instagram_url' => 'nullable|string|max:2048',
            'youtube_url' => 'nullable|string|max:2048',
            'footer_text' => 'nullable|string|max:5000',
            'footer_quick_links_title' => 'nullable|string|max:255',
            'copyright_text' => 'nullable|string|max:500',
            'site_active' => 'nullable|boolean',
            'chat_active' => 'nullable|boolean',
            'affiliate_active' => 'nullable|boolean',
            'affiliate_minimum_withdraw_amount' => 'nullable|numeric|min:0',
            'affiliate_minimum_order_amount' => 'nullable|numeric|min:0',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:4096',
            'favicon' => 'nullable|image|mimes:jpg,jpeg,png,webp,ico|max:2048',
            'footer_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:4096',
        ]);

        $setting = $this->getSetting();

        foreach (['logo', 'favicon', 'footer_logo'] as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('images'), $filename);
                $validated[$field] = 'images/'.$filename;
            } else {
                unset($validated[$field]);
            }
        }

        $validated['site_active'] = $request->boolean('site_active');
        $validated['chat_active'] = $request->boolean('chat_active');
        $validated['affiliate_active'] = $request->boolean('affiliate_active');
        $validated['affiliate_minimum_withdraw_amount'] = $validated['affiliate_minimum_withdraw_amount'] ?? (float) ($setting->affiliate_minimum_withdraw_amount ?? 500);
        $validated['affiliate_minimum_order_amount'] = $validated['affiliate_minimum_order_amount'] ?? (float) ($setting->affiliate_minimum_order_amount ?? 0);

        $setting->update($validated);

        return redirect()->route($routeName)->with('success', $message);
    }
}
