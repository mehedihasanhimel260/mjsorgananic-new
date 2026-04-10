<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
            'alternative_phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'ip_address' => 'nullable|string|max:45',
            'ip_division' => 'nullable|string|max:255',
            'ip_district' => 'nullable|string|max:255',
            'ip_thana' => 'nullable|string|max:255',
            'ip_postcode' => 'nullable|string|max:255',
            'gps_lat' => 'nullable|numeric|between:-90,90',
            'gps_lng' => 'nullable|numeric|between:-180,180',
            'gps_address' => 'nullable|string|max:2000',
            'saved_division' => 'nullable|string|max:255',
            'saved_district' => 'nullable|string|max:255',
            'saved_thana' => 'nullable|string|max:255',
            'saved_postcode' => 'nullable|string|max:255',
            'saved_address' => 'nullable|string|max:2000',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }
}
