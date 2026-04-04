@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="card xl:col-span-1">
            <header class="card-header"><p class="card-header-title"><span class="icon"><i class="mdi mdi-key-plus"></i></span>Create Permission</p></header>
            <div class="card-content">
                <form method="POST" action="{{ route('admin.permissions.store') }}">
                    @csrf
                    <div class="field"><label class="label">Permission Name</label><input class="input" type="text" name="name" required></div>
                    <div class="field"><label class="label">Slug</label><input class="input" type="text" name="slug" placeholder="optional"></div>
                    <button type="submit" class="button green">Create Permission</button>
                </form>
            </div>
        </div>
        <div class="card has-table xl:col-span-2">
            <header class="card-header"><p class="card-header-title"><span class="icon"><i class="mdi mdi-key-chain"></i></span>Permissions</p></header>
            <div class="card-content">
                <table>
                    <thead><tr><th>Name</th><th>Slug</th><th>Edit</th></tr></thead>
                    <tbody>
                        @forelse($permissions as $permission)
                        <tr>
                            <td>{{ $permission->name }}</td>
                            <td>{{ $permission->slug }}</td>
                            <td><a href="{{ route('admin.permissions.edit', $permission) }}" class="button small blue"><span class="icon"><i class="mdi mdi-pencil"></i></span></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="has-text-centered">No permissions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
