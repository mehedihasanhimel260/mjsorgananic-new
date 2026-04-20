@extends('layouts.admin_main')

@section('content')
@php
    $nullStatusCount = $orders->getCollection()->filter(fn ($order) => blank($order->order_status))->count();
    $inReviewCount = $orders->getCollection()->where('order_status', 'in_review')->count();
    $pendingCount = $orders->getCollection()->where('order_status', 'pending')->count();
@endphp

<section class="section main-section">
    <div class="mx-4 mb-6 overflow-hidden rounded-[28px] border border-emerald-100 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.24),_transparent_34%),linear-gradient(135deg,#064e3b_0%,#0f766e_42%,#0ea5a4_100%)] text-white shadow-[0_24px_80px_rgba(15,118,110,0.24)]">
        <div class="grid gap-6 px-6 py-7 lg:grid-cols-[1.4fr,1fr] lg:px-8">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-emerald-100/90">অর্ডার বোর্ড</p>
                <h1 class="mt-3 text-2xl font-black leading-tight lg:text-4xl">প্রায়োরিটি অনুযায়ী অর্ডার দেখুন এবং প্রতিটি স্ট্যাটাসে কী কাজ করতে হবে তা দ্রুত বুঝুন</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-emerald-50 lg:text-base">
                    আগে <strong>null status</strong>, তারপর <strong>in_review</strong>, তারপর <strong>pending</strong>, এরপর বাকি অর্ডার নতুন থেকে পুরোনো ক্রমে দেখানো হবে। এই বোর্ডে প্রতিটি স্ট্যাটাসের পরের কাজও পরিষ্কারভাবে ধরা আছে।
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-2xl border border-white/10 bg-white/12 p-4 backdrop-blur">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-emerald-100">এই পেজে</p>
                    <p class="mt-2 text-3xl font-black">{{ $orders->count() }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/12 p-4 backdrop-blur">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-emerald-100">মোট অর্ডার</p>
                    <p class="mt-2 text-3xl font-black">{{ $orders->total() }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/12 p-4 backdrop-blur">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-emerald-100">নাল স্ট্যাটাস</p>
                    <p class="mt-2 text-2xl font-black">{{ $nullStatusCount }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/12 p-4 backdrop-blur">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-emerald-100">ইন রিভিউ / পেন্ডিং</p>
                    <p class="mt-2 text-2xl font-black">{{ $inReviewCount }} / {{ $pendingCount }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-4 card overflow-hidden rounded-3xl border border-slate-200 shadow-sm">
        <header class="card-header border-b border-slate-100 bg-slate-50/80">
            <div class="flex w-full flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <p class="card-header-title !mb-0">
                    <span class="icon"><i class="mdi mdi-cart-outline"></i></span>
                    Orders
                </p>
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        Page {{ $orders->currentPage() }} of {{ $orders->lastPage() }}
                    </span>
                    <a href="{{ route('admin.orders.index') }}" class="button blue">
                        <span class="icon"><i class="mdi mdi-refresh"></i></span>
                        <span>Refresh</span>
                    </a>
                </div>
            </div>
        </header>

        <div class="card-content">
            <div class="mb-5 rounded-[26px] border border-slate-200 bg-[linear-gradient(135deg,#f8fafc_0%,#eef6ff_100%)] p-4 shadow-sm md:p-5">
                <form method="GET" action="{{ route('admin.orders.index') }}" class="grid gap-4 xl:grid-cols-[1.1fr,0.9fr]">
                    <div class="grid gap-4 md:grid-cols-[1fr,220px]">
                        <div class="field mb-0">
                            <label class="label">Customer phone search</label>
                            <div class="control">
                                <input class="input rounded-xl border-slate-200 bg-white" type="text" name="phone_search" value="{{ $phoneSearch ?? request('phone_search') }}" placeholder="Enter full or partial customer phone number">
                            </div>
                            <p class="help">Customer-এর phone number লিখলে matching order show হবে। Partial number দিলেও search কাজ করবে।</p>
                        </div>
                        <div class="rounded-2xl border border-sky-100 bg-white px-4 py-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-600">Quick Tip</p>
                            <p class="mt-2 text-sm leading-6 text-sky-800">Example: `01712`, `8801`, `66127`</p>
                        </div>
                    </div>
                    <div class="flex flex-col justify-end gap-2 sm:flex-row xl:flex-col xl:items-stretch">
                        <button type="submit" class="button green w-full xl:w-auto">Search Order</button>
                        <a href="{{ route('admin.orders.index') }}" class="button blue w-full xl:w-auto justify-center">
                            <span class="icon"><i class="mdi mdi-refresh"></i></span>
                            <span>Reset</span>
                        </a>
                    </div>
                </form>
            </div>

            <div class="mb-5 flex flex-wrap gap-3">
                <span class="rounded-full bg-rose-50 px-4 py-2 text-xs font-semibold text-rose-700">Null Status: order confirm করতে হবে</span>
                <span class="rounded-full bg-blue-50 px-4 py-2 text-xs font-semibold text-blue-700">In Review: product ready করে courier-এ দিতে হবে</span>
                <span class="rounded-full bg-amber-50 px-4 py-2 text-xs font-semibold text-amber-700">Pending: delivery man assign হয়েছে কি না check করতে হবে</span>
                <span class="rounded-full bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700">Assign হলে customer আর delivery man-এর যোগাযোগ করাতে হবে</span>
            </div>

            <div class="hidden xl:block overflow-x-auto rounded-[24px] border border-slate-100">
                <table>
                    <thead>
                        <tr>
                            <th>SI</th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Products</th>
                            <th>Courier</th>
                            <th>Affiliate</th>
                            <th>Created</th>
                            <th>View</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            @php
                                $orderStatus = $order->order_status;
                                $statusLabel = $orderStatus ? ucfirst(str_replace('_', ' ', $orderStatus)) : 'Null Status';
                                $statusClass = match ($orderStatus) {
                                    null => 'bg-rose-100 text-rose-700',
                                    'in_review' => 'bg-blue-100 text-blue-700',
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    'delivered' => 'bg-emerald-100 text-emerald-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                                $productSummary = $order->items->isNotEmpty()
                                    ? $order->items->take(2)->map(fn ($item) => ($item->product?->name ?? 'Product').' x'.$item->quantity)->join(', ')
                                    : 'N/A';
                                $extraProductCount = max($order->items->count() - 2, 0);
                            @endphp
                            <tr>
                                <td>{{ $orders->firstItem() + $loop->index }}</td>
                                <td data-label="Order">
                                    <div class="space-y-1">
                                        <p class="font-bold text-slate-900">{{ $order->order_number }}</p>
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ ucfirst($order->order_type ?? 'Order') }}</p>
                                    </div>
                                </td>
                                <td data-label="Customer">
                                    <div class="space-y-1">
                                        <p class="font-semibold text-slate-900">{{ $order->user?->name ?? 'N/A' }}</p>
                                        <p class="text-sm text-slate-600">{{ $order->user?->phone ?? 'N/A' }}</p>
                                    </div>
                                </td>
                                <td data-label="Amount">
                                    <div class="space-y-1">
                                        <p class="font-black text-slate-900">{{ number_format((float) $order->total_amount, 2) }}</p>
                                        <p class="text-xs text-slate-400">{{ $order->items->sum('quantity') }} pcs total</p>
                                    </div>
                                </td>
                                <td data-label="Status">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td data-label="Products">
                                    <div class="space-y-1">
                                        <p class="text-sm text-slate-700">{{ $productSummary }}</p>
                                        @if($extraProductCount > 0)
                                            <p class="text-xs font-semibold text-slate-400">+{{ $extraProductCount }} more item(s)</p>
                                        @endif
                                    </div>
                                </td>
                                <td data-label="Courier">
                                    <div class="space-y-1">
                                        <p class="text-sm font-semibold text-slate-700">{{ $order->track_id ?? 'Track ID N/A' }}</p>
                                        <p class="text-xs text-slate-400">Parcel ID: {{ data_get($order->courier_api_response, 'consignment.consignment_id', 'N/A') }}</p>
                                    </div>
                                </td>
                                <td data-label="Affiliate">{{ $order->affiliate?->name ?? 'Direct' }}</td>
                                <td data-label="Created">
                                    <div class="space-y-1">
                                        <p class="text-sm font-semibold text-slate-700">{{ $order->created_at->format('Y-m-d') }}</p>
                                        <p class="text-xs text-slate-400">{{ $order->created_at->format('h:i A') }}</p>
                                    </div>
                                </td>
                                <td class="actions-cell">
                                    <div class="buttons right nowrap">
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="button small blue" type="button">
                                            <span class="icon"><i class="mdi mdi-eye"></i></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="has-text-centered">No orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="space-y-4 xl:hidden">
                @forelse ($orders as $order)
                    @php
                        $orderStatus = $order->order_status;
                        $statusLabel = $orderStatus ? ucfirst(str_replace('_', ' ', $orderStatus)) : 'Null Status';
                        $statusClass = match ($orderStatus) {
                            null => 'bg-rose-100 text-rose-700',
                            'in_review' => 'bg-blue-100 text-blue-700',
                            'pending' => 'bg-amber-100 text-amber-700',
                            'delivered' => 'bg-emerald-100 text-emerald-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                            default => 'bg-slate-100 text-slate-700',
                        };
                    @endphp
                    <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-4 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-lg font-black text-slate-900">{{ $order->order_number }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $order->user?->name ?? 'N/A' }} • {{ $order->user?->phone ?? 'N/A' }}</p>
                            </div>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Amount</p>
                                <p class="mt-1 font-bold text-slate-800">{{ number_format((float) $order->total_amount, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Affiliate</p>
                                <p class="mt-1 text-slate-700">{{ $order->affiliate?->name ?? 'Direct' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Track ID</p>
                                <p class="mt-1 text-slate-700">{{ $order->track_id ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Consignment</p>
                                <p class="mt-1 text-slate-700">{{ data_get($order->courier_api_response, 'consignment.consignment_id', 'N/A') }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Products</p>
                                <p class="mt-1 leading-6 text-slate-700">
                                    @if($order->items->isNotEmpty())
                                        {{ $order->items->map(fn($item) => ($item->product?->name ?? 'Product').' '.$item->quantity.'pcs')->join(', ') }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Created</p>
                                <p class="mt-1 text-slate-700">{{ $order->created_at->format('Y-m-d h:i A') }}</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="button blue w-full">
                                <span class="icon"><i class="mdi mdi-eye"></i></span>
                                <span>View Order</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-slate-500">
                        No orders found.
                    </div>
                @endforelse
            </div>

            <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
