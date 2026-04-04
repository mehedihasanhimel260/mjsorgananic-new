@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card">
        <header class="card-header"><p class="card-header-title"><span class="icon"><i class="mdi mdi-pencil"></i></span>Edit Role</p></header>
        <div class="card-content">
            <form method="POST" action="{{ route('admin.roles.update', $role) }}">
                @csrf
                @method('PATCH')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="field"><label class="label">Role Name</label><input class="input" type="text" name="name" value="{{ old('name', $role->name) }}" required></div>
                    <div class="field"><label class="label">Slug</label><input class="input" type="text" name="slug" value="{{ old('slug', $role->slug) }}" required></div>
                </div>
                <div class="field mt-4"><label class="label">Permissions</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-80 overflow-y-auto">
                        @foreach($permissions as $permission)
                            <label class="checkbox block"><input type="checkbox" name="permissions[]" value="{{ $permission->id }}" {{ $role->permissions->contains('id', $permission->id) ? 'checked' : '' }}> <span class="ml-2">{{ $permission->name }}</span></label>
                        @endforeach
                    </div>
                </div>
                <div class="mt-6 flex gap-3"><button type="submit" class="button green">Update Role</button><a href="{{ route('admin.roles.index') }}" class="button blue">Back</a></div>
            </form>
        </div>
    </div>
</section>
@endsection
