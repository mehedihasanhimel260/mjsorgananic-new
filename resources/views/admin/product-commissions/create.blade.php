@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-plus-circle-outline"></i></span>
                Add Product Commission
            </p>
        </header>
        <div class="card-content">
            <form method="POST" action="{{ route('admin.product-commissions.store') }}">
                @csrf
                @include('admin.product-commissions.partials.form', ['commission' => null, 'selectedProductId' => $selectedProductId ?? null])
            </form>
        </div>
    </div>
</section>
@endsection
