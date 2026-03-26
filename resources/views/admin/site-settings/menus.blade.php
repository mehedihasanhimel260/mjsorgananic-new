@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card mb-6">
        <header class="card-header"><p class="card-header-title"><span class="icon"><i class="mdi mdi-menu"></i></span>Header / Menu Settings</p></header>
        <div class="card-content">
            <form method="POST" action="{{ route('admin.site-settings.menus.store') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input class="input" type="text" name="title" placeholder="Menu title" required>
                    <select class="input" name="menu_type" required>
                        <option value="internal_page">Internal Page</option>
                        <option value="custom_link">Custom Link</option>
                        <option value="category">Category</option>
                        <option value="product_section">Product Section</option>
                    </select>
                    <input class="input" type="number" name="sort_order" placeholder="Sort order" value="0">
                    <select class="input" name="target_slug"><option value="">Select page slug</option>@foreach($pages as $page)<option value="{{ $page->slug }}">{{ $page->title }}</option>@endforeach</select>
                    <input class="input" type="text" name="url" placeholder="Custom URL / target">
                    <select class="input" name="parent_id"><option value="">No Parent</option>@foreach($parentMenus as $parent)<option value="{{ $parent->id }}">{{ $parent->title }}</option>@endforeach</select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <label class="checkbox"><input type="checkbox" name="is_visible" value="1" checked> <span class="check"></span><span class="control-label">Visible in Menu</span></label>
                    <label class="checkbox"><input type="checkbox" name="open_in_new_tab" value="1"> <span class="check"></span><span class="control-label">Open in New Tab</span></label>
                </div>
                <div class="field grouped mt-4"><div class="control"><button type="submit" class="button green">Add Menu Item</button></div></div>
            </form>
        </div>
    </div>

    <div class="card has-table">
        <header class="card-header"><p class="card-header-title"><span class="icon"><i class="mdi mdi-format-list-bulleted"></i></span>Existing Menu Items</p></header>
        <div class="card-content">
            <table>
                <thead><tr><th>Title</th><th>Type</th><th>Target</th><th>Sort</th><th>Visible</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($menus as $menu)
                    <tr>
                        <td>{{ $menu->title }}</td>
                        <td>{{ $menu->menu_type }}</td>
                        <td>{{ $menu->target_slug ?: $menu->url ?: 'N/A' }}</td>
                        <td>{{ $menu->sort_order }}</td>
                        <td>{{ $menu->is_visible ? 'Yes' : 'No' }}</td>
                        <td><a href="{{ route('admin.site-settings.menus.edit', $menu->id) }}" class="button small blue">Edit</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="has-text-centered">No menu item found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
