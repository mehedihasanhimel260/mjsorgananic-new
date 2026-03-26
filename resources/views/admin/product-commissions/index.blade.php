@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-cash-multiple"></i></span>
                Product Commissions
            </p>
            <a href="{{ route('admin.product-commissions.create') }}" class="button blue">
                <span class="icon"><i class="mdi mdi-plus"></i></span>
                <span>Add Commission</span>
            </a>
        </header>
        <div class="card-content">
            <table>
                <thead>
                    <tr>
                        <th>SI</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Commission Type</th>
                        <th>Commission Value</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($commissions as $commission)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td data-label="Product">{{ $commission->product?->name ?? 'N/A' }}</td>
                        <td data-label="Price">{{ $commission->product?->selling_price ?? 'N/A' }}</td>
                        <td data-label="Commission Type">{{ ucfirst($commission->commission_type) }}</td>
                        <td data-label="Commission Value">{{ $commission->commission_value }}</td>
                        <td data-label="Status">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $commission->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($commission->status) }}
                            </span>
                        </td>
                        <td data-label="Created">
                            <small class="text-gray-500" title="{{ $commission->created_at }}">{{ $commission->created_at->format('Y-m-d') }}</small>
                        </td>
                        <td class="actions-cell">
                            <div class="buttons right nowrap">
                                <a href="{{ route('admin.product-commissions.edit', $commission->id) }}" class="button small blue" type="button">
                                    <span class="icon"><i class="mdi mdi-pencil"></i></span>
                                </a>
                                <form action="{{ route('admin.product-commissions.toggle-status', $commission->id) }}" method="POST">
                                    @csrf
                                    <button class="button small {{ $commission->status === 'active' ? 'red' : 'green' }}" type="submit">
                                        <span class="icon"><i class="mdi {{ $commission->status === 'active' ? 'mdi-toggle-switch-off-outline' : 'mdi-toggle-switch-outline' }}"></i></span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="has-text-centered">No commissions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
