@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card">
        <header class="card-header"><p class="card-header-title"><span class="icon"><i class="mdi mdi-pencil"></i></span>Edit Menu Item</p></header>
        <div class="card-content">
            <form method="POST" action="{{ route('admin.site-settings.menus.update', $siteMenu->id) }}">
                @csrf
                @method('PATCH')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input class="input" type="text" name="title" value="{{ old('title', $siteMenu->title) }}" required>
                    <select class="input" name="menu_type" required>
                        @foreach(['internal_page','custom_link','category','product_section'] as $type)
                        <option value="{{ $type }}" {{ old('menu_type', $siteMenu->menu_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    <input class="input" type="number" name="sort_order" value="{{ old('sort_order', $siteMenu->sort_order) }}">
                    <select class="input" name="target_slug"><option value="">Select page slug</option>@foreach($pages as $page)<option value="{{ $page->slug }}" {{ old('target_slug', $siteMenu->target_slug) === $page->slug ? 'selected' : '' }}>{{ $page->title }}</option>@endforeach</select>
                    <input class="input" type="text" name="url" value="{{ old('url', $siteMenu->url) }}">
                    <select class="input" name="parent_id"><option value="">No Parent</option>@foreach($parentMenus as $parent)<option value="{{ $parent->id }}" {{ (string) old('parent_id', $siteMenu->parent_id) === (string) $parent->id ? 'selected' : '' }}>{{ $parent->title }}</option>@endforeach</select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <label class="checkbox"><input type="checkbox" name="is_visible" value="1" {{ old('is_visible', $siteMenu->is_visible) ? 'checked' : '' }}> <span class="check"></span><span class="control-label">Visible in Menu</span></label>
                    <label class="checkbox"><input type="checkbox" name="open_in_new_tab" value="1" {{ old('open_in_new_tab', $siteMenu->open_in_new_tab) ? 'checked' : '' }}> <span class="check"></span><span class="control-label">Open in New Tab</span></label>
                </div>
                <div class="field grouped mt-4"><div class="control"><button type="submit" class="button green">Update Menu</button></div><div class="control"><a href="{{ route('admin.site-settings.menus') }}" class="button red">Cancel</a></div></div>
            </form>
        </div>
    </div>
</section>
@endsection
