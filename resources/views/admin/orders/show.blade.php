@extends('layouts.admin_main')

@section('content')
@php
    $productTotal = $order->items->sum(fn($item) => $item->quantity * $item->sell_price);
    $grandTotal = $productTotal + ($order->delivery_charge ?? 0) - ($order->discount_amount ?? 0);
    $courierResponse = $order->courier_api_response ?? [];
    $bookingSuccess = ($courierResponse['status'] ?? null) === 200;
@endphp
<section class="section main-section">
    <div class="card mb-6">
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
                <div>
                    <h3 class="title is-5 mb-4">Order Information</h3>
                    <p><strong>Order No:</strong> {{ $order->order_number }}</p>
                    <p><strong>Type:</strong> {{ ucfirst($order->order_type) }}</p>
                    <p><strong>Total:</strong> {{ $order->total_amount }}</p>
                    <p><strong>Order Status:</strong> {{ $order->order_status ?? 'N/A' }}</p>
                    <p><strong>Track ID:</strong> {{ $order->track_id ?? 'N/A' }}</p>
                    <p><strong>Courier API Response:</strong> {{ $bookingSuccess ? 'Received' : 'Not booked yet' }}</p>
                    <p><strong>Created:</strong> {{ $order->created_at->format('Y-m-d H:i') }}</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <form action="{{ route('admin.orders.status.update', $order->id) }}" method="POST" onsubmit="return confirm('Mark this order as cancelled?')">
                            @csrf
                            <input type="hidden" name="order_status" value="cancelled">
                            <button type="submit" class="button red">
                                <span class="icon"><i class="mdi mdi-cancel"></i></span>
                                <span>Mark Cancelled</span>
                            </button>
                        </form>
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
                <div>
                    <h3 class="title is-5 mb-4">Customer Information</h3>
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
                @if (! $bookingSuccess)
                <form action="{{ route('admin.orders.book-courier', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="button green">
                        <span class="icon"><i class="mdi mdi-truck-fast-outline"></i></span>
                        <span>Booking</span>
                    </button>
                </form>
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
                <tfoot>
                    <tr>
                        <th colspan="5" class="has-text-right">Product Total</th>
                        <th>{{ $productTotal }}</th>
                    </tr>
                    <tr>
                        <th colspan="5" class="has-text-right">Delivery Charge</th>
                        <th>{{ $order->delivery_charge ?? 0 }}</th>
                    </tr>
                    <tr>
                        <th colspan="5" class="has-text-right">Discount Amount</th>
                        <th>{{ $order->discount_amount ?? 0 }}</th>
                    </tr>
                    <tr>
                        <th colspan="5" class="has-text-right">Grand Total</th>
                        <th>{{ $grandTotal }}</th>
                    </tr>
                </tfoot>
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
