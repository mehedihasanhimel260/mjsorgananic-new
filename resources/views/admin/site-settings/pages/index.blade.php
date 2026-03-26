@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
<div class="card has-table">
<header class="card-header">
<p class="card-header-title"><span class="icon"><i class="mdi mdi-file-document-outline"></i></span>Page Settings</p>
<a href="{{ route('admin.site-settings.pages.create') }}" class="button blue"><span class="icon"><i class="mdi mdi-plus"></i></span><span>Add Page</span></a>
</header>
<div class="card-content">
<table>
<thead><tr><th>Title</th><th>Slug</th><th>Status</th><th>Show in Menu</th><th>Action</th></tr></thead>
<tbody>
@forelse($pages as $page)
<tr>
<td>{{ $page->title }}</td><td>{{ $page->slug }}</td><td>{{ ucfirst($page->status) }}</td><td>{{ $page->show_in_menu ? 'Yes' : 'No' }}</td>
<td><a href="{{ route('admin.site-settings.pages.edit', $page->id) }}" class="button small blue">Edit</a></td>
</tr>
@empty
<tr><td colspan="5" class="has-text-centered">No page found.</td></tr>
@endforelse
</tbody>
</table>
<div class="mt-6">{{ $pages->links() }}</div>
</div>
</div>
</section>
@endsection
