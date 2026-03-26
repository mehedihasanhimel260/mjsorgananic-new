<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function profile()
    {
        $affiliate = auth()->guard('affiliate')->user();

        return view('affiliates.account.profile', compact('affiliate'));
    }

    public function updateProfile(Request $request)
    {
        $affiliate = auth()->guard('affiliate')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('affiliates', 'email')->ignore($affiliate->id)],
            'phone' => ['required', 'string', 'max:20', Rule::unique('affiliates', 'phone')->ignore($affiliate->id)],
        ]);

        $affiliate->update($validated);

        return redirect()->route('affiliates.account.profile')->with('success', 'Profile updated successfully.');
    }

    public function settings()
    {
        $affiliate = auth()->guard('affiliate')->user();

        return view('affiliates.account.settings', compact('affiliate'));
    }

    public function updateSettings(Request $request)
    {
        $affiliate = auth()->guard('affiliate')->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (! Hash::check($validated['current_password'], $affiliate->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        $affiliate->update([
            'password' => $validated['password'],
        ]);

        return redirect()->route('affiliates.account.settings')->with('success', 'Password updated successfully.');
    }
}
