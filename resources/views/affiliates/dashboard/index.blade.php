@extends('affiliates.layouts.app')

@section('content')
<section class="section main-section">
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div class="card">
            <div class="card-content">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Links</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $summary['total_links'] }}</p>
                    </div>
                    <span class="icon text-blue-500"><i class="mdi mdi-link-variant mdi-36px"></i></span>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Affiliate Orders</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $summary['total_orders'] }}</p>
                    </div>
                    <span class="icon text-green-500"><i class="mdi mdi-cart-check mdi-36px"></i></span>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Commission</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($summary['total_commission'], 2) }}</p>
                    </div>
                    <span class="icon text-emerald-500"><i class="mdi mdi-currency-bdt mdi-36px"></i></span>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Current Balance</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($summary['balance'], 2) }}</p>
                    </div>
                    <span class="icon text-yellow-500"><i class="mdi mdi-wallet-outline mdi-36px"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4 mt-6">
        <div class="card">
            <div class="card-content">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Wallet Credit</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['wallet_credit'], 2) }}</p>
                    </div>
                    <span class="icon text-green-500"><i class="mdi mdi-arrow-down-bold-circle-outline mdi-36px"></i></span>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Wallet Debit</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['wallet_debit'], 2) }}</p>
                    </div>
                    <span class="icon text-red-500"><i class="mdi mdi-arrow-up-bold-circle-outline mdi-36px"></i></span>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Affiliate Code</p>
                        <p class="text-xl font-bold text-gray-900">{{ $affiliate->affiliate_code }}</p>
                    </div>
                    <span class="icon text-purple-500"><i class="mdi mdi-card-account-details-outline mdi-36px"></i></span>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Pending Withdraw</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['pending_withdraw'], 2) }}</p>
                    </div>
                    <span class="icon text-orange-500"><i class="mdi mdi-timer-sand mdi-36px"></i></span>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Withdrawn</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_withdrawn'], 2) }}</p>
                    </div>
                    <span class="icon text-red-500"><i class="mdi mdi-bank-transfer-out mdi-36px"></i></span>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Quick Action</p>
                        <a href="{{ route('affiliates.links.index') }}" class="button blue mt-2">
                            <span class="icon"><i class="mdi mdi-link-plus"></i></span>
                            <span>Create Link</span>
                        </a>
                    </div>
                    <span class="icon text-blue-500"><i class="mdi mdi-rocket-launch-outline mdi-36px"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card has-table mt-6">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-link-variant"></i></span>
                Recent Affiliate Links
            </p>
            <a href="{{ route('affiliates.links.index') }}" class="button blue">
                <span class="icon"><i class="mdi mdi-eye"></i></span>
                <span>View All</span>
            </a>
        </header>
        <div class="card-content">
            <table>
                <thead>
                    <tr>
                        <th>SI</th>
                        <th>Product</th>
                        <th>Tracking Code</th>
                        <th>Share URL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($links as $link)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $link->product?->name ?? 'N/A' }}</td>
                        <td>{{ $link->tracking_code }}</td>
                        <td class="text-sm">{{ get_affiliate_share_url($link) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="has-text-centered">No affiliate link created yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card has-table mt-6">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-package-variant-closed"></i></span>
                Product List
            </p>
        </header>
        <div class="card-content">
            <table>
                <thead>
                    <tr>
                        <th>SI</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Commission Type</th>
                        <th>Commission Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($commissionProducts as $commissionProduct)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $commissionProduct->product?->name ?? 'N/A' }}</td>
                        <td>{{ number_format((float) ($commissionProduct->product?->selling_price ?? 0), 2) }}</td>
                        <td>{{ ucfirst($commissionProduct->commission_type) }}</td>
                        <td>
                            @if ($commissionProduct->commission_type === 'fixed')
                                Tk {{ number_format((float) $commissionProduct->commission_value, 2) }}
                            @else
                                {{ number_format((float) $commissionProduct->commission_value, 2) }}%
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="has-text-centered">No commission-enabled product found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="affiliate-orders" class="card has-table mt-6">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-cart-outline"></i></span>
                Recent Affiliate Orders
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
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentOrders as $order)
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
                        <td>{{ optional($order->created_at)->format('Y-m-d h:i A') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="has-text-centered">No affiliate order yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="commission-history" class="card has-table mt-6">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-cash-multiple"></i></span>
                Commission History
            </p>
        </header>
        <div class="card-content">
            <table>
                <thead>
                    <tr>
                        <th>Order No</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Commission</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentCommissions as $commission)
                    <tr>
                        <td>{{ $commission->order?->order_number ?? 'N/A' }}</td>
                        <td>{{ $commission->product?->name ?? 'N/A' }}</td>
                        <td>{{ ucfirst($commission->commission_type) }}</td>
                        <td>{{ number_format((float) $commission->commission_value, 2) }}</td>
                        <td>{{ number_format((float) $commission->commission_amount, 2) }}</td>
                        <td>{{ optional($commission->created_at)->format('Y-m-d h:i A') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="has-text-centered">No commission history found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="wallet-history" class="card has-table mt-6">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-wallet-outline"></i></span>
                Wallet Transactions
            </p>
        </header>
        <div class="card-content">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($walletTransactions as $transaction)
                    <tr>
                        <td>
                            <span class="{{ $transaction->type === 'credit' ? 'text-green-600' : 'text-red-600' }} font-semibold">
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </td>
                        <td>{{ number_format((float) $transaction->amount, 2) }}</td>
                        <td>{{ $transaction->description }}</td>
                        <td>{{ optional($transaction->created_at)->format('Y-m-d h:i A') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="has-text-centered">No wallet transaction found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="withdraw-history" class="card has-table mt-6">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-cash-fast"></i></span>
                Recent Withdraw Requests
            </p>
            <a href="{{ route('affiliates.wallet.index') }}#withdraw-request" class="button blue">
                <span class="icon"><i class="mdi mdi-eye"></i></span>
                <span>Manage</span>
            </a>
        </header>
        <div class="card-content">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Requested</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($withdrawRequests as $request)
                    <tr>
                        <td>#{{ $request->id }}</td>
                        <td>{{ number_format((float) $request->amount, 2) }}</td>
                        <td>{{ ucfirst($request->account_type) }}</td>
                        <td>{{ ucfirst($request->status) }}</td>
                        <td>{{ optional($request->requested_at)->format('Y-m-d h:i A') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="has-text-centered">No withdraw request found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
