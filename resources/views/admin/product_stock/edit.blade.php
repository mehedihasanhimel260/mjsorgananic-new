@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-pencil"></i></span>
                Edit Stock Batch
            </p>
        </header>
        <div class="card-content">
            <form method="post" action="{{ route('admin.product-stocks.update', $product_stock->id) }}">
                @csrf
                @method('PATCH')
                <div class="field">
                    <label class="label">Product</label>
                    <div class="control">
                        <div class="select">
                            <select name="product_id">
                                @foreach($products as $product)
                                <option value="{{ $product->id }}" @if($product_stock->product_id == $product->id) selected @endif>{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Quantity</label>
                    <div class="control">
                        <input class="input" type="number" name="quantity" placeholder="Quantity" value="{{ $product_stock->quantity }}" required>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Total Cost</label>
                    <div class="control">
                        <input class="input" type="number" step="0.01" name="total_cost" placeholder="Total Cost" value="{{ $product_stock->total_cost }}" required>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Note</label>
                    <div class="control">
                        <textarea class="textarea" name="note" placeholder="Note">{{ $product_stock->note }}</textarea>
                    </div>
                </div>
                <hr>
                <div class="field grouped">
                    <div class="control">
                        <button type="submit" class="button green">
                            Update
                        </button>
                    </div>
                    <div class="control">
                        <a href="{{ route('admin.product-stocks.index') }}" class="button red">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection