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
                        <th>Status</th>
                        <th>Track ID</th>
                        <th>Products</th>
                        <th>Total</th>
                        <th>Delivery</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                    @php
                        $isBooked = !empty($order->track_id);
                        $isCancelled = $order->order_status === 'cancelled';
                        $statusClass = match ($order->order_status) {
                            'cancelled' => 'bg-red-100 text-red-700',
                            'delivered' => 'bg-green-100 text-green-700',
                            'partial_delivered' => 'bg-yellow-100 text-yellow-700',
                            'in_review', 'submitted' => 'bg-blue-100 text-blue-700',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    @endphp
                    <tr>
                        <td class="font-semibold text-gray-800">{{ $order->order_number }}</td>
                        <td>{{ $order->user?->name ?? 'N/A' }}</td>
                        <td>{{ $order->user?->phone ?? 'N/A' }}</td>
                        <td>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                {{ ucfirst(str_replace('_', ' ', $order->order_status ?? 'pending')) }}
                            </span>
                        </td>
                        <td>
                            @if($order->track_id)
                                <a href="{{ data_get($order->courier_api_response, 'consignment.tracking_link', 'https://steadfast.com.bd/t/'.$order->track_id) }}" target="_blank" class="text-blue-600 hover:underline font-semibold">
                                    {{ $order->track_id }}
                                </a>
                            @else
                                <span class="text-gray-400">N/A</span>
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
                        <td>
                            <div class="flex flex-col gap-2 min-w-[150px]">
                                @if (! $isBooked && ! $isCancelled)
                                    <form action="{{ route('affiliates.orders.book-courier', $order) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="button small green w-full">
                                            <span class="icon"><i class="mdi mdi-truck-fast-outline"></i></span>
                                            <span>Book Courier</span>
                                        </button>
                                    </form>
                                    <form action="{{ route('affiliates.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Cancel this order before courier booking?')">
                                        @csrf
                                        <button type="submit" class="button small red w-full">
                                            <span class="icon"><i class="mdi mdi-cancel"></i></span>
                                            <span>Cancel Order</span>
                                        </button>
                                    </form>
                                @elseif ($isBooked)
                                    <button type="button" class="button small green w-full opacity-70 cursor-default" disabled>
                                        <span class="icon"><i class="mdi mdi-check-circle-outline"></i></span>
                                        <span>Booked</span>
                                    </button>
                                    <button type="button" class="button small red w-full opacity-50 cursor-not-allowed" disabled>
                                        <span class="icon"><i class="mdi mdi-lock-outline"></i></span>
                                        <span>Cancel Disabled</span>
                                    </button>
                                @else
                                    <button type="button" class="button small gray w-full opacity-70 cursor-default" disabled>
                                        <span class="icon"><i class="mdi mdi-close-circle-outline"></i></span>
                                        <span>Cancelled</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="has-text-centered">No affiliate order found.</td>
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
