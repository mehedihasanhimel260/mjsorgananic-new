@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-plus-box-outline"></i></span>
                Create Landing Page
            </p>
        </header>
        <div class="card-content">
            <form method="POST" action="{{ route('admin.landing-pages.store') }}" enctype="multipart/form-data">
                @csrf
                @include('admin.landing-pages.partials.form')
            </form>
        </div>
    </div>
</section>
@endsection
