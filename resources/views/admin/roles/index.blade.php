@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="card xl:col-span-1">
            <header class="card-header"><p class="card-header-title"><span class="icon"><i class="mdi mdi-shield-account"></i></span>Create Role</p></header>
            <div class="card-content">
                <form method="POST" action="{{ route('admin.roles.store') }}">
                    @csrf
                    <div class="field"><label class="label">Role Name</label><input class="input" type="text" name="name" required></div>
                    <div class="field"><label class="label">Slug</label><input class="input" type="text" name="slug" placeholder="optional"></div>
                    <div class="field"><label class="label">Permissions</label>
                        <div class="space-y-2 max-h-72 overflow-y-auto">
                            @foreach($permissions as $permission)
                                <label class="checkbox block"><input type="checkbox" name="permissions[]" value="{{ $permission->id }}"> <span class="ml-2">{{ $permission->name }}</span></label>
                            @endforeach
                        </div>
                    </div>
                    <button type="submit" class="button green">Create Role</button>
                </form>
            </div>
        </div>
        <div class="card has-table xl:col-span-2">
            <header class="card-header"><p class="card-header-title"><span class="icon"><i class="mdi mdi-format-list-bulleted"></i></span>Roles</p></header>
            <div class="card-content">
                <table>
                    <thead><tr><th>Name</th><th>Slug</th><th>Permissions</th><th>Edit</th></tr></thead>
                    <tbody>
                        @forelse($roles as $role)
                        <tr>
                            <td>{{ $role->name }}</td>
                            <td>{{ $role->slug }}</td>
                            <td>{{ $role->permissions->pluck('name')->join(', ') ?: 'N/A' }}</td>
                            <td><a href="{{ route('admin.roles.edit', $role) }}" class="button small blue"><span class="icon"><i class="mdi mdi-pencil"></i></span></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="has-text-centered">No roles found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
