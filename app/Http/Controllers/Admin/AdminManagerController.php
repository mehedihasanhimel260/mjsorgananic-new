<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminManagerController extends Controller
{
    public function index()
    {
        $admins = Admin::with('roles')->latest()->get();
        $roles = Role::orderBy('name')->get();

        return view('admin.admins.index', compact('admins', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:admins,email',
            'phone' => 'required|string|max:20|unique:admins,phone',
            'password' => 'required|string|min:6|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $admin = Admin::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
        ]);

        $admin->roles()->sync($validated['roles'] ?? []);

        return back()->with('success', 'Staff admin created successfully.');
    }

    public function updateRoles(Request $request, Admin $admin)
    {
        $validated = $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $admin->roles()->sync($validated['roles'] ?? []);

        return back()->with('success', 'Admin roles updated successfully.');
    }
}
