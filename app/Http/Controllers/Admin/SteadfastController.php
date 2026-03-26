<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SteadfastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SteadfastController extends Controller
{
    public function __construct(private readonly SteadfastService $steadfastService)
    {
    }

    public function index()
    {
        $setting = $this->steadfastService->getSetting();

        return view('admin.steadfast.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'api_key' => 'required|string|max:255',
            'secret_key' => 'required|string|max:255',
        ]);

        $setting = $this->steadfastService->getSetting();
        $setting->update($validated);

        return redirect()->route('admin.steadfast.index')->with('success', 'Steadfast credentials updated successfully.');
    }

    public function refreshBalance()
    {
        try {
            $result = $this->steadfastService->refreshBalance();

            return redirect()->route('admin.steadfast.index')
                ->with($result['success'] ? 'success' : 'error', $result['message']);
        } catch (\Throwable $exception) {
            return redirect()->route('admin.steadfast.index')->with('error', 'Could not connect to Steadfast: '.$exception->getMessage());
        }
    }

    public function runSchedule()
    {
        Artisan::call('steadfast:sync-status');

        return response()->json([
            'status' => 'success',
            'message' => 'Steadfast sync completed.',
            'output' => trim(Artisan::output()),
        ]);
    }
}
