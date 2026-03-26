@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-cart-outline"></i></span>
                Products
            </p>
            <a href="{{ route('admin.products.create') }}" class="button blue">
                <span class="icon"><i class="mdi mdi-plus"></i></span>
                <span>Create Product</span>
            </a>
        </header>
        <div class="card-content">
            <table>
                <thead>
                    <tr>
                        <th>SI</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>SKU</th>
                        <th>Keywords</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td data-label="Image">
                            @if($product->image)
                            <img src="{{ asset('images/'.$product->image) }}" alt="{{ $product->name }}" style="width: 50px; height: auto;">
                            @else
                            No Image
                            @endif
                        </td>
                        <td data-label="Name">{{ $product->name }}</td>
                        <td data-label="Category">{{ $product->category->name }}</td>
                        <td data-label="SKU">{{ $product->sku }}</td>
                        <td data-label="Keywords">{{ $product->keywords }}</td>
                        <td data-label="Price">{{ $product->selling_price }}</td>
                        <td data-label="Status">{{ $product->status }}</td>
                        <td data-label="Created">
                            <small class="text-gray-500" title="{{ $product->created_at }}">{{ $product->created_at->format('Y-m-d') }}</small>
                        </td>
                        <td class="actions-cell">
                            <div class="buttons right nowrap">
                                <a href="{{ route('admin.product-commissions.create', ['product_id' => $product->id]) }}" class="button small green" type="button" title="Set Commission">
                                    <span class="icon"><i class="mdi mdi-cash-plus"></i></span>
                                </a>
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="button small blue" type="button">
                                    <span class="icon"><i class="mdi mdi-pencil"></i></span>
                                </a>
                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
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
            <div class="table-pagination">
                {{-- Add pagination links if you have pagination in your controller --}}
            </div>
        </div>
    </div>
</section>
@endsection
