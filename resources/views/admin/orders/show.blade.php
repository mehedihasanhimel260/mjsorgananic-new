@extends('layouts.admin_main')

@section('content')
@php
    $productTotal = $order->items->sum(fn($item) => $item->quantity * $item->sell_price);
    $grandTotal = $productTotal + ($order->delivery_charge ?? 0) - ($order->discount_amount ?? 0);
    $courierResponse = $order->courier_api_response ?? [];
    $bookingSuccess = ($courierResponse['status'] ?? null) === 200 || !empty($order->track_id);
    $orderStatus = $order->order_status ?? 'pending';
    $canBook = ! $bookingSuccess && $orderStatus !== 'cancelled';
    $statusClass = match ($orderStatus) {
        'cancelled' => 'bg-red-100 text-red-700',
        'delivered' => 'bg-green-100 text-green-700',
        'partial_delivered' => 'bg-yellow-100 text-yellow-700',
        'in_review', 'submitted' => 'bg-blue-100 text-blue-700',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp
<section class="section main-section">
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        <div class="card xl:col-span-2">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-receipt"></i></span>
                    Order Details
                </p>
                <a href="{{ route('admin.orders.index') }}" class="button blue">
                    <span class="icon"><i class="mdi mdi-arrow-left"></i></span>
                    <span>Back</span>
                </a>
            </header>
            <div class="card-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-5">
                        <h3 class="title is-5 mb-4">Order Information</h3>
                        <div class="space-y-3 text-sm text-gray-700">
                            <p><strong>Order No:</strong> {{ $order->order_number }}</p>
                            <p><strong>Type:</strong> {{ ucfirst($order->order_type) }}</p>
                            <p><strong>Total:</strong> {{ number_format((float) $order->total_amount, 2) }}</p>
                            <p>
                                <strong>Order Status:</strong>
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $orderStatus)) }}
                                </span>
                            </p>
                            <p><strong>Track ID:</strong> {{ $order->track_id ?? 'N/A' }}</p>
                            <p><strong>Courier API Response:</strong> {{ $bookingSuccess ? 'Received' : 'Not booked yet' }}</p>
                            <p><strong>Created:</strong> {{ $order->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-5">
                        <h3 class="title is-5 mb-4">Customer Information</h3>
                        <div class="space-y-3 text-sm text-gray-700">
                            <p>
                                <strong>Name:</strong>
                                @if ($order->user)
                                <a href="{{ route('admin.users.edit', $order->user->id) }}" class="text-blue-600 hover:underline">
                                    {{ $order->user->name }}
                                </a>
                                @else
                                N/A
                                @endif
                            </p>
                            <p><strong>Phone:</strong> {{ $order->user?->phone ?? 'N/A' }}</p>
                            <p><strong>Address:</strong> {{ $order->user?->saved_address ?? 'N/A' }}</p>
                            <p><strong>Affiliate:</strong> {{ $order->affiliate?->name ?? 'Direct Order' }}</p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 flex flex-wrap gap-3">
                    @if ($canBook)
                    <form action="{{ route('admin.orders.status.update', $order->id) }}" method="POST" onsubmit="return confirm('Mark this order as cancelled?')">
                        @csrf
                        <input type="hidden" name="order_status" value="cancelled">
                        <button type="submit" class="button red">
                            <span class="icon"><i class="mdi mdi-cancel"></i></span>
                            <span>Mark Cancelled</span>
                        </button>
                    </form>
                    @else
                    <button type="button" class="button red opacity-50 cursor-not-allowed" disabled>
                        <span class="icon"><i class="mdi mdi-lock-outline"></i></span>
                        <span>Cancel Unavailable</span>
                    </button>
                    @endif
                    @if ($order->track_id)
                    <form action="{{ route('admin.orders.sync-courier-status', $order->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="button blue">
                            <span class="icon"><i class="mdi mdi-refresh"></i></span>
                            <span>Sync Courier Status</span>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-cash-multiple"></i></span>
                    Order Summary
                </p>
            </header>
            <div class="card-content space-y-4">
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 flex items-center justify-between">
                    <span class="text-gray-500">Product Total</span>
                    <strong>{{ number_format((float) $productTotal, 2) }}</strong>
                </div>
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 flex items-center justify-between">
                    <span class="text-gray-500">Delivery Charge</span>
                    <strong>{{ number_format((float) ($order->delivery_charge ?? 0), 2) }}</strong>
                </div>
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 flex items-center justify-between">
                    <span class="text-gray-500">Discount</span>
                    <strong>{{ number_format((float) ($order->discount_amount ?? 0), 2) }}</strong>
                </div>
                <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 flex items-center justify-between text-blue-900">
                    <span class="font-semibold">Grand Total</span>
                    <strong>{{ number_format((float) $grandTotal, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card has-table mb-6">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-package-variant"></i></span>
                Ordered Products
            </p>
            <div class="buttons right nowrap">
                <button type="button" class="button blue" onclick="document.getElementById('discount-form').classList.toggle('hidden')">
                    <span class="icon"><i class="mdi mdi-percent"></i></span>
                    <span>Total Discount</span>
                </button>
                @if ($canBook)
                <form action="{{ route('admin.orders.book-courier', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="button green">
                        <span class="icon"><i class="mdi mdi-truck-fast-outline"></i></span>
                        <span>Booking</span>
                    </button>
                </form>
                @else
                <button type="button" class="button green opacity-70 cursor-default" disabled>
                    <span class="icon"><i class="mdi mdi-check-circle-outline"></i></span>
                    <span>Courier Booked / Locked</span>
                </button>
                @endif
            </div>
        </header>
        <div class="card-content">
            <form action="{{ route('admin.orders.items.add', $order->id) }}" method="POST" class="mb-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="label">Add Product</label>
                        <div class="control">
                            <select name="product_id" class="input" required>
                                <option value="">Select product</option>
                                @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} - {{ $product->selling_price }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="label">Quantity</label>
                        <div class="control">
                            <input class="input" type="number" name="quantity" min="1" value="1" required>
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="button green w-full">
                            <span class="icon"><i class="mdi mdi-plus"></i></span>
                            <span>Add Product</span>
                        </button>
                    </div>
                </div>
            </form>
            @if ($deliverySetting)
            <form action="{{ route('admin.orders.delivery-charge.update', $order->id) }}" method="POST" class="mb-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="label">Delivery Charge</label>
                        <div class="flex flex-wrap gap-4">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="delivery_location" value="inside_dhaka" {{ (float) ($order->delivery_charge ?? 0) === (float) ($deliverySetting->inside_dhaka_delivery_charge ?? 0) ? 'checked' : '' }}>
                                <span>Inside Dhaka ({{ $deliverySetting->inside_dhaka_delivery_charge ?? 0 }})</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="delivery_location" value="outside_dhaka" {{ (float) ($order->delivery_charge ?? 0) === (float) ($deliverySetting->outside_dhaka_delivery_charge ?? 0) ? 'checked' : '' }}>
                                <span>Outside Dhaka ({{ $deliverySetting->outside_dhaka_delivery_charge ?? 0 }})</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="button blue w-full">
                            <span class="icon"><i class="mdi mdi-truck-check-outline"></i></span>
                            <span>Update Delivery</span>
                        </button>
                    </div>
                </div>
            </form>
            @endif
            <form id="discount-form" action="{{ route('admin.orders.discount.update', $order->id) }}" method="POST" class="hidden mb-4">
                @csrf
                <div class="field grouped">
                    <div class="control">
                        <input class="input" type="number" step="0.01" min="0" name="discount_amount" value="{{ old('discount_amount', $order->discount_amount ?? 0) }}" placeholder="Enter discount amount" required>
                    </div>
                    <div class="control">
                        <button type="submit" class="button green">Update</button>
                    </div>
                </div>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>SI</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Sell Price</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td data-label="Product">{{ $item->product?->name ?? 'N/A' }}</td>
                        <td data-label="Quantity">
                            <form action="{{ route('admin.orders.items.update', [$order->id, $item->id]) }}" method="POST" class="flex gap-2 items-center">
                                @csrf
                                <input class="input" type="number" name="quantity" min="1" value="{{ $item->quantity }}" style="max-width: 90px;" required>
                                <button type="submit" class="button small blue">
                                    <span class="icon"><i class="mdi mdi-check"></i></span>
                                </button>
                            </form>
                        </td>
                        <td data-label="Sell Price">{{ $item->sell_price }}</td>
                        <td data-label="Subtotal">{{ $item->quantity * $item->sell_price }}</td>
                        <td data-label="Action">
                            <form action="{{ route('admin.orders.items.remove', [$order->id, $item->id]) }}" method="POST" onsubmit="return confirm('Remove this product from the order?')">
                                @csrf
                                <button type="submit" class="button small red">
                                    <span class="icon"><i class="mdi mdi-delete"></i></span>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($bookingSuccess)
            <details class="mt-6 border rounded-lg bg-gray-50" open>
                <summary class="cursor-pointer px-4 py-3 font-semibold text-gray-700">Steadfast Booking Response</summary>
                <div class="p-4">
                    <table>
                        <tbody>
                            <tr>
                                <th>Consignment ID</th>
                                <td>{{ $courierResponse['consignment']['consignment_id'] ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Invoice</th>
                                <td>{{ $courierResponse['consignment']['invoice'] ?? $order->order_number }}</td>
                            </tr>
                            <tr>
                                <th>Tracking Code</th>
                                <td>{{ $courierResponse['consignment']['tracking_code'] ?? $order->track_id ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Recipient Name</th>
                                <td>{{ $courierResponse['consignment']['recipient_name'] ?? $order->user?->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Recipient Phone</th>
                                <td>{{ $courierResponse['consignment']['recipient_phone'] ?? $order->user?->phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Recipient Address</th>
                                <td>{{ $courierResponse['consignment']['recipient_address'] ?? $order->user?->saved_address ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>COD Amount</th>
                                <td>{{ $courierResponse['consignment']['cod_amount'] ?? $grandTotal }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>{{ $courierResponse['consignment']['status'] ?? $order->order_status ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Item Description</th>
                                <td>{{ $courierResponse['item_description'] ?? $order->items->map(fn($item) => ($item->product?->name ?? 'Product').', '.$item->quantity.'pcs')->join(' | ') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </details>
            @endif
        </div>
    </div>

    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-archive-arrow-down"></i></span>
                Stock Out Logs
            </p>
        </header>
        <div class="card-content">
            <table>
                <thead>
                    <tr>
                        <th>SI</th>
                        <th>Product ID</th>
                        <th>Batch ID</th>
                        <th>Quantity</th>
                        <th>Cost Per Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($order->stockOutLogs as $log)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td data-label="Product ID">{{ $log->product_id }}</td>
                        <td data-label="Batch ID">{{ $log->batch_id }}</td>
                        <td data-label="Quantity">{{ $log->quantity }}</td>
                        <td data-label="Cost Per Unit">{{ $log->cost_per_unit }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="has-text-centered">No stock out logs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

