@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-cash-refund"></i></span>
                Affiliate Withdraw Requests
            </p>
        </header>
        <div class="card-content">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Affiliate</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Number</th>
                        <th>Status</th>
                        <th>Requested</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($withdrawRequests as $withdrawRequest)
                    <tr>
                        <td>#{{ $withdrawRequest->id }}</td>
                        <td>{{ $withdrawRequest->affiliate?->name ?? 'N/A' }}</td>
                        <td>{{ number_format((float) $withdrawRequest->amount, 2) }}</td>
                        <td>{{ ucfirst($withdrawRequest->account_type) }}</td>
                        <td>{{ $withdrawRequest->account_number }}</td>
                        <td>{{ ucfirst($withdrawRequest->status) }}</td>
                        <td>{{ optional($withdrawRequest->requested_at)->format('Y-m-d h:i A') }}</td>
                        <td class="actions-cell">
                            <div class="buttons right nowrap">
                                <a href="{{ route('admin.affiliate-withdraws.show', $withdrawRequest) }}" class="button small blue">
                                    <span class="icon"><i class="mdi mdi-eye"></i></span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="has-text-centered">No withdraw request found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-6">
                {{ $withdrawRequests->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
