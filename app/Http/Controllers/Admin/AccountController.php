<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function profile()
    {
        $admin = auth()->guard('admin')->user();

        return view('admin.account.profile', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        $admin = auth()->guard('admin')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin->id)],
            'phone' => ['required', 'string', 'max:20', Rule::unique('admins', 'phone')->ignore($admin->id)],
        ]);

        $admin->update($validated);

        return redirect()->route('admin.account.profile')->with('success', 'Profile updated successfully.');
    }

    public function settings()
    {
        $admin = auth()->guard('admin')->user();

        return view('admin.account.settings', compact('admin'));
    }

    public function updateSettings(Request $request)
    {
        $admin = auth()->guard('admin')->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (! Hash::check($validated['current_password'], $admin->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        $admin->update([
            'password' => $validated['password'],
        ]);

        return redirect()->route('admin.account.settings')->with('success', 'Password updated successfully.');
    }
}
