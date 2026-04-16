<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class AdminMaintenanceController extends Controller
{
    public function clearApplicationData(Request $request): RedirectResponse
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('optimize:clear');

        clear_affiliate_attribution($request);

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Cookie::queue(Cookie::forget('XSRF-TOKEN'));
        Cookie::queue(Cookie::forget(config('session.cookie')));

        return redirect()->route('admin.login')->with('success', 'Application cache and current browser session data cleared successfully. Please log in again.');
    }
}
