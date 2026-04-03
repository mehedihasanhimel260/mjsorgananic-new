<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AffiliateController extends Controller
{
    public function index()
    {
        $affiliates = Affiliate::withCount(['links', 'commissions', 'walletTransactions'])
            ->latest()
            ->paginate(20);

        return view('admin.affiliates.index', compact('affiliates'));
    }

    public function impersonate(Request $request, Affiliate $affiliate)
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            abort(403);
        }

        Auth::guard('affiliate')->login($affiliate);
        $request->session()->put('impersonated_by_admin_id', $admin->id);
        $request->session()->regenerate();

        return redirect()->route('affiliates.dashboard')->with('success', 'You are now logged in as affiliate: '.$affiliate->name);
    }
}
