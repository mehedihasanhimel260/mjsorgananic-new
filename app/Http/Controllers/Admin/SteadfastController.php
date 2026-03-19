<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SteadfastSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SteadfastController extends Controller
{
    private const BASE_URL = 'https://portal.packzy.com/api/v1';

    private function getSetting(): SteadfastSetting
    {
        return SteadfastSetting::firstOrCreate([], [
            'current_balance' => 0,
        ]);
    }

    public function index()
    {
        $setting = $this->getSetting();

        return view('admin.steadfast.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'api_key' => 'required|string|max:255',
            'secret_key' => 'required|string|max:255',
        ]);

        $setting = $this->getSetting();
        $setting->update($validated);

        return redirect()->route('admin.steadfast.index')->with('success', 'Steadfast credentials updated successfully.');
    }

    public function refreshBalance()
    {
        $setting = $this->getSetting();

        if (! $setting->api_key || ! $setting->secret_key) {
            return redirect()->route('admin.steadfast.index')->with('error', 'Please save Api-Key and Secret-Key first.');
        }

        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'Api-Key' => $setting->api_key,
                    'Secret-Key' => $setting->secret_key,
                    'Content-Type' => 'application/json',
                ])
                ->get(self::BASE_URL.'/get_balance');

            if (! $response->successful()) {
                return redirect()->route('admin.steadfast.index')->with('error', 'Steadfast balance refresh failed.');
            }

            $data = $response->json();

            if (! isset($data['current_balance'])) {
                return redirect()->route('admin.steadfast.index')->with('error', 'Invalid balance response from Steadfast.');
            }

            $setting->update([
                'current_balance' => $data['current_balance'],
                'last_balance_synced_at' => now(),
            ]);

            return redirect()->route('admin.steadfast.index')->with('success', 'Steadfast balance refreshed successfully.');
        } catch (\Throwable $exception) {
            return redirect()->route('admin.steadfast.index')->with('error', 'Could not connect to Steadfast: '.$exception->getMessage());
        }
    }
}
