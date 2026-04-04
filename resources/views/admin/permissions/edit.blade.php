@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card">
        <header class="card-header"><p class="card-header-title"><span class="icon"><i class="mdi mdi-pencil"></i></span>Edit Permission</p></header>
        <div class="card-content">
            <form method="POST" action="{{ route('admin.permissions.update', $permission) }}">
                @csrf
                @method('PATCH')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="field"><label class="label">Permission Name</label><input class="input" type="text" name="name" value="{{ old('name', $permission->name) }}" required></div>
                    <div class="field"><label class="label">Slug</label><input class="input" type="text" name="slug" value="{{ old('slug', $permission->slug) }}" required></div>
                </div>
                <div class="mt-6 flex gap-3"><button type="submit" class="button green">Update Permission</button><a href="{{ route('admin.permissions.index') }}" class="button blue">Back</a></div>
            </form>
        </div>
    </div>
</section>
@endsection
