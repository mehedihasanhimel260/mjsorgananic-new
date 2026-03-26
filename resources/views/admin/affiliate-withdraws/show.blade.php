@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-cash-refund"></i></span>
                    Withdraw Request #{{ $affiliateWithdrawRequest->id }}
                </p>
            </header>
            <div class="card-content">
                <table>
                    <tbody>
                        <tr><th>Affiliate</th><td>{{ $affiliateWithdrawRequest->affiliate?->name ?? 'N/A' }}</td></tr>
                        <tr><th>Email</th><td>{{ $affiliateWithdrawRequest->affiliate?->email ?? 'N/A' }}</td></tr>
                        <tr><th>Phone</th><td>{{ $affiliateWithdrawRequest->affiliate?->phone ?? 'N/A' }}</td></tr>
                        <tr><th>Amount</th><td>{{ number_format((float) $affiliateWithdrawRequest->amount, 2) }}</td></tr>
                        <tr><th>Payment Method</th><td>{{ ucwords(str_replace('_', ' ', $affiliateWithdrawRequest->payment_method)) }}</td></tr>
                        <tr><th>Account Type</th><td>{{ ucfirst($affiliateWithdrawRequest->account_type) }}</td></tr>
                        <tr><th>Account Number</th><td>{{ $affiliateWithdrawRequest->account_number }}</td></tr>
                        <tr><th>Account Name</th><td>{{ $affiliateWithdrawRequest->account_name }}</td></tr>
                        <tr><th>Status</th><td>{{ ucfirst($affiliateWithdrawRequest->status) }}</td></tr>
                        <tr><th>Requested At</th><td>{{ optional($affiliateWithdrawRequest->requested_at)->format('Y-m-d h:i A') }}</td></tr>
                        <tr><th>Approved At</th><td>{{ optional($affiliateWithdrawRequest->approved_at)->format('Y-m-d h:i A') ?: 'N/A' }}</td></tr>
                        <tr><th>Paid At</th><td>{{ optional($affiliateWithdrawRequest->paid_at)->format('Y-m-d h:i A') ?: 'N/A' }}</td></tr>
                        <tr><th>Admin Note</th><td>{{ $affiliateWithdrawRequest->admin_note ?: 'N/A' }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-tune"></i></span>
                    Actions
                </p>
            </header>
            <div class="card-content space-y-6">
                @if ($affiliateWithdrawRequest->status === \App\Models\AffiliateWithdrawRequest::STATUS_PENDING)
                    <form action="{{ route('admin.affiliate-withdraws.approve', $affiliateWithdrawRequest) }}" method="POST">
                        @csrf
                        <label class="label">Approve Note</label>
                        <textarea name="admin_note" class="textarea" rows="3">{{ old('admin_note') }}</textarea>
                        <button type="submit" class="button green mt-3 w-full">Approve Request</button>
                    </form>

                    <form action="{{ route('admin.affiliate-withdraws.reject', $affiliateWithdrawRequest) }}" method="POST">
                        @csrf
                        <label class="label">Reject Note</label>
                        <textarea name="admin_note" class="textarea" rows="3"></textarea>
                        <button type="submit" class="button red mt-3 w-full">Reject Request</button>
                    </form>
                @elseif ($affiliateWithdrawRequest->status === \App\Models\AffiliateWithdrawRequest::STATUS_APPROVED)
                    <form action="{{ route('admin.affiliate-withdraws.mark-paid', $affiliateWithdrawRequest) }}" method="POST">
                        @csrf
                        <label class="label">Payment Note</label>
                        <textarea name="admin_note" class="textarea" rows="3">{{ old('admin_note', $affiliateWithdrawRequest->admin_note) }}</textarea>
                        <button type="submit" class="button blue mt-3 w-full">Mark As Paid</button>
                    </form>
                @else
                    <div class="notification">
                        This request is already {{ $affiliateWithdrawRequest->status }}. No more actions are available.
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
