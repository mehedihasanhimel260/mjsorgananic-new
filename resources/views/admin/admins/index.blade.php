@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="card xl:col-span-1">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-account-plus-outline"></i></span>
                    Create Staff
                </p>
            </header>
            <div class="card-content">
                <form method="POST" action="{{ route('admin.admins.store') }}">
                    @csrf
                    <div class="field">
                        <label class="label">Name</label>
                        <div class="control">
                            <input class="input" type="text" name="name" value="{{ old('name') }}" required>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Email</label>
                        <div class="control">
                            <input class="input" type="email" name="email" value="{{ old('email') }}">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Phone</label>
                        <div class="control">
                            <input class="input" type="text" name="phone" value="{{ old('phone') }}" required>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Password</label>
                        <div class="control">
                            <input class="input" type="password" name="password" required>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Confirm Password</label>
                        <div class="control">
                            <input class="input" type="password" name="password_confirmation" required>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Assign Roles</label>
                        <div class="space-y-2 max-h-64 overflow-y-auto border rounded p-3">
                            @forelse($roles as $role)
                                <label class="checkbox block">
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" {{ collect(old('roles', []))->contains($role->id) ? 'checked' : '' }}>
                                    <span class="ml-2">{{ $role->name }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500">No role created yet. Create roles first.</p>
                            @endforelse
                        </div>
                    </div>
                    <button type="submit" class="button green">Create Staff</button>
                </form>
            </div>
        </div>

        <div class="card has-table xl:col-span-2">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-account-cog-outline"></i></span>
                    Admin Role Assignment
                </p>
            </header>
            <div class="card-content">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Current Roles</th>
                            <th>Manage Access</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                        <tr>
                            <td>{{ $admin->name }}</td>
                            <td>{{ $admin->email ?? 'N/A' }}</td>
                            <td>{{ $admin->phone }}</td>
                            <td>{{ $admin->roles->pluck('name')->join(', ') ?: 'Full access (no role assigned)' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.admins.roles.update', $admin) }}">
                                    @csrf
                                    <div class="grid grid-cols-1 gap-2 mb-3 max-h-40 overflow-y-auto border rounded p-3">
                                        @foreach($roles as $role)
                                        <label class="checkbox block">
                                            <input type="checkbox" name="roles[]" value="{{ $role->id }}" {{ $admin->roles->contains('id', $role->id) ? 'checked' : '' }}>
                                            <span class="ml-2">{{ $role->name }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                    <button type="submit" class="button small green">Update Roles</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="has-text-centered">No admin found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
