@extends('layouts.admin_main')

@section('content')
    <section class="section main-section">
        <div class="card has-table">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-cart-outline"></i></span>
                    Orders
                </p>
                <a href="{{ route('admin.orders.index') }}" class="button blue">
                    <span class="icon"><i class="mdi mdi-refresh"></i></span>
                    <span>Refresh</span>
                </a>
            </header>
            <div class="card-content">
                <table>
                    <thead>
                        <tr>
                            <th>SI</th>
                            <th>Order No</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Track ID</th>
                            <th>Total</th>
                            <th>Order Status</th>
                            <th>Products</th>
                            <th>Quantity</th>
                            <th>Affiliate</th>
                            <th>Created</th>
                            <th>View</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td data-label="Order No">{{ $order->order_number }}</td>
                                <td data-label="Customer">{{ $order->user?->name ?? 'N/A' }}</td>
                                <td data-label="Phone">{{ $order->user?->phone ?? 'N/A' }}</td>
                                <td data-label="Type">
                                    @if ($order->track_id)
                                        <a href="https://steadfast.com.bd/t/{{ $order->track_id }}" target="_blank"
                                            class="text-blue-600 hover:underline">
                                            {{ $order->track_id }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td data-label="Total">{{ $order->total_amount }}</td>
                                <td data-label="Order Status">{{ $order->order_status ?? 'Pending' }}</td>
                                <td data-label="Products">
                                    {{ $order->items->pluck('product.name')->filter()->join(', ') ?: 'N/A' }}
                                </td>
                                <td data-label="Quantity">{{ $order->items->sum('quantity') }}</td>
                                <td data-label="Affiliate">{{ $order->affiliate?->name ?? 'Direct' }}</td>
                                <td data-label="Created">
                                    <small class="text-gray-500"
                                        title="{{ $order->created_at }}">{{ $order->created_at->format('Y-m-d') }}</small>
                                </td>
                                <td class="actions-cell">
                                    <div class="buttons right nowrap">
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="button small blue"
                                            type="button">
                                            <span class="icon"><i class="mdi mdi-eye"></i></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="has-text-centered">No orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="table-pagination">
                </div>
            </div>
        </div>
    </section>
@endsection
