@extends('affiliates.layouts.app')

@section('content')
<section class="section main-section">
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
                        <th>Description</th>
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

            <div class="mt-6">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
