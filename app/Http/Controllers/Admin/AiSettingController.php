<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiSettingController extends Controller
{
    private function ensureDefaultRows(): void
    {
        $defaults = [
            ['title' => 'OpenAI'],
            ['title' => 'Gemini'],
            ['title' => 'Claude'],
        ];

        foreach ($defaults as $default) {
            AiSetting::firstOrCreate(
                ['title' => $default['title']],
                [
                    'api_key' => null,
                    'model_name' => null,
                    'is_active' => false,
                ]
            );
        }
    }

    public function index()
    {
        $this->ensureDefaultRows();

        $settings = AiSetting::orderBy('title')->get();

        return view('admin.ai-settings.index', compact('settings'));
    }

    public function edit(AiSetting $aiSetting)
    {
        return view('admin.ai-settings.edit', compact('aiSetting'));
    }

    public function update(Request $request, AiSetting $aiSetting)
    {
        $validated = $request->validate([
            'api_key' => 'nullable|string|max:5000',
            'model_name' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $isActive = (bool) ($validated['is_active'] ?? false);
        $apiKey = array_key_exists('api_key', $validated) ? trim((string) $validated['api_key']) : null;

        DB::transaction(function () use ($aiSetting, $validated, $isActive, $apiKey) {
            if ($isActive) {
                AiSetting::where('id', '!=', $aiSetting->id)->update(['is_active' => false]);
            }

            $aiSetting->update([
                'api_key' => $apiKey !== '' ? $apiKey : $aiSetting->api_key,
                'model_name' => $validated['model_name'] ?? null,
                'is_active' => $isActive,
            ]);
        });

        return redirect()->route('admin.ai-settings.index')->with('success', 'AI setting updated successfully.');
    }
}
