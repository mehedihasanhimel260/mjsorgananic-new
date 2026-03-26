@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-pencil-circle-outline"></i></span>
                Edit Product Commission
            </p>
        </header>
        <div class="card-content">
            <form method="POST" action="{{ route('admin.product-commissions.update', $productCommission->id) }}">
                @csrf
                @method('PATCH')
                @include('admin.product-commissions.partials.form', ['commission' => $productCommission, 'selectedProductId' => null])
            </form>
        </div>
    </div>
</section>
@endsection
