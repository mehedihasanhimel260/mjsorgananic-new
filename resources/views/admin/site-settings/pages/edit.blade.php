@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
<div class="card"><header class="card-header"><p class="card-header-title"><span class="icon"><i class="mdi mdi-pencil-outline"></i></span>Edit Page</p></header><div class="card-content">
@include('admin.site-settings.pages.partials.form', ['action' => route('admin.site-settings.pages.update', $sitePage->id), 'method' => 'PATCH', 'sitePage' => $sitePage])
</div></div>
</section>
@endsection
