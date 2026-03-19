@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-package-variant-closed"></i></span>
                Product Stock Batches
            </p>
            <a href="{{ route('admin.product-stocks.create') }}" class="button blue">
                <span class="icon"><i class="mdi mdi-plus"></i></span>
                <span>Add Stock</span>
            </a>
        </header>
        <div class="card-content">
            <table>
                <thead>
                    <tr>
                        <th>SI</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Total Cost</th>
                        <th>Cost Per Unit</th>
                        <th>Note</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stockBatches as $batch)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td data-label="Product">{{ $batch->product->name }}</td>
                        <td data-label="Quantity">{{ $batch->quantity }}</td>
                        <td data-label="Total Cost">{{ $batch->total_cost }}</td>
                        <td data-label="Cost Per Unit">{{ $batch->cost_per_unit }}</td>
                        <td data-label="Note">{{ $batch->note }}</td>
                        <td data-label="Created">
                            <small class="text-gray-500" title="{{ $batch->created_at }}">{{ $batch->created_at->format('Y-m-d') }}</small>
                        </td>
                        <td class="actions-cell">
                            <div class="buttons right nowrap">
                                <a href="{{ route('admin.product-stocks.edit', $batch->id) }}" class="button small blue" type="button">
                                    <span class="icon"><i class="mdi mdi-pencil"></i></span>
                                </a>
                                <form action="{{ route('admin.product-stocks.destroy', $batch->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this stock batch?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="button small red" type="submit">
                                        <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection