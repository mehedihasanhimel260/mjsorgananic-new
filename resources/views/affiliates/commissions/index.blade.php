@extends('affiliates.layouts.app')

@section('content')
<section class="section main-section">
    <div class="card has-table">
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
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($commissions as $commission)
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

            <div class="mt-6">
                {{ $commissions->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
