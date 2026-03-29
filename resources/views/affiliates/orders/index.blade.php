@extends('affiliates.layouts.app')

@section('content')
<section class="section main-section">
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-cart-outline"></i></span>
                Affiliate Orders
            </p>
        </header>
        <div class="card-content">
            <table>
                <thead>
                    <tr>
                        <th>Order No</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Track ID</th>
                        <th>Products</th>
                        <th>Total</th>
                        <th>Delivery</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->user?->name ?? 'N/A' }}</td>
                        <td>{{ $order->user?->phone ?? 'N/A' }}</td>
                        <td>
                            @if($order->track_id)
                                <a href="https://steadfast.com.bd/t/{{ $order->track_id }}" target="_blank" class="text-blue-600 hover:underline">
                                    {{ $order->track_id }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @foreach ($order->items as $item)
                                {{ $item->product?->name ?? 'Product' }} ({{ $item->quantity }})@if (! $loop->last), @endif
                            @endforeach
                        </td>
                        <td>{{ number_format((float) $order->total_amount, 2) }}</td>
                        <td>{{ number_format((float) ($order->delivery_charge ?? 0), 2) }}</td>
                        <td>{{ optional($order->created_at)->format('Y-m-d h:i A') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="has-text-centered">No affiliate order found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
