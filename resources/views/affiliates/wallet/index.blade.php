@extends('affiliates.layouts.app')

@section('content')
<section class="section main-section">
    <div class="grid gap-6 md:grid-cols-3 mb-6">
        <div class="card">
            <div class="card-content">
                <p class="text-sm text-gray-500">Available Balance</p>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($summary['balance'], 2) }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <p class="text-sm text-gray-500">Pending Withdraw</p>
                <p class="text-3xl font-bold text-yellow-600">{{ number_format($summary['pending_withdraw'], 2) }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-content">
                <p class="text-sm text-gray-500">Total Withdrawn</p>
                <p class="text-3xl font-bold text-green-600">{{ number_format($summary['total_withdrawn'], 2) }}</p>
            </div>
        </div>
    </div>

    <div id="withdraw-request" class="card mb-6">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-cash-fast"></i></span>
                Withdraw Request
            </p>
        </header>
        <div class="card-content">
            <form action="{{ route('affiliates.wallet.withdraw.store') }}" method="POST">
                @csrf
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="label">Amount</label>
                        <div class="control">
                            <input type="number" name="amount" min="{{ $summary['minimum_withdraw'] }}" step="0.01" class="input" value="{{ old('amount') }}" required>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Minimum withdraw amount: {{ number_format($summary['minimum_withdraw'], 2) }} BDT</p>
                    </div>
                    <div>
                        <label class="label">Account Type</label>
                        <div class="control">
                            <div class="select w-full">
                                <select name="account_type" required>
                                    <option value="">Select account type</option>
                                    <option value="bkash" @selected(old('account_type') === 'bkash')>Bkash</option>
                                    <option value="nagad" @selected(old('account_type') === 'nagad')>Nagad</option>
                                    <option value="rocket" @selected(old('account_type') === 'rocket')>Rocket</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="label">Account Number</label>
                        <div class="control">
                            <input type="text" name="account_number" class="input" value="{{ old('account_number') }}" required>
                        </div>
                    </div>
                    <div>
                        <label class="label">Account Name</label>
                        <div class="control">
                            <input type="text" name="account_name" class="input" value="{{ old('account_name') }}" required>
                        </div>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="button blue">
                        <span class="icon"><i class="mdi mdi-send"></i></span>
                        <span>Submit Withdraw Request</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card has-table mb-6">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-history"></i></span>
                Recent Withdraw Requests
            </p>
        </header>
        <div class="card-content">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Number</th>
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
                        <td>{{ $request->account_number }}</td>
                        <td>{{ ucfirst($request->status) }}</td>
                        <td>{{ optional($request->requested_at)->format('Y-m-d h:i A') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="has-text-centered">No withdraw request found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-6">
                {{ $withdrawRequests->links() }}
            </div>
        </div>
    </div>

    <div class="card has-table">
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
                        <th>Order</th>
                        <th>Remark</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                    <tr>
                        <td>
                            <span class="{{ $transaction->type === 'credit' ? 'text-green-600' : 'text-red-600' }} font-semibold">
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </td>
                        <td>{{ number_format((float) $transaction->amount, 2) }}</td>
                        <td>{{ $transaction->order?->order_number ?? 'N/A' }}</td>
                        <td>{{ $transaction->description }}</td>
                        <td>{{ optional($transaction->created_at)->format('Y-m-d h:i A') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="has-text-centered">No wallet transaction found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-6">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
