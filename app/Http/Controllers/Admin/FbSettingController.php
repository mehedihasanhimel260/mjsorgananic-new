<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FbSetting;
use Illuminate\Http\Request;

class FbSettingController extends Controller
{
    private function getSetting(): FbSetting
    {
        return FbSetting::firstOrCreate([]);
    }

    public function index()
    {
        $setting = $this->getSetting();

        return view('admin.fb-settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'fb_page_id' => 'nullable|string|max:255',
            'access_token' => 'nullable|string|max:5000',
            'pixel_id' => 'nullable|string|max:255',
            'event_id' => 'nullable|string|max:255',
            'verify_token' => 'nullable|string|max:255',
        ]);

        $setting = $this->getSetting();
        $setting->update($validated);

        return redirect()->route('admin.fb-settings.index')->with('success', 'Facebook settings updated successfully.');
    }
}
