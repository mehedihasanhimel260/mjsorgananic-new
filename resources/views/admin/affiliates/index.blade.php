@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-account-star-outline"></i></span>
                Affiliate Accounts
            </p>
        </header>
        <div class="card-content">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Affiliate Code</th>
                        <th>Balance</th>
                        <th>Links</th>
                        <th>Commissions</th>
                        <th>Transactions</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($affiliates as $affiliate)
                    <tr>
                        <td class="font-semibold text-gray-800">{{ $affiliate->name }}</td>
                        <td>{{ $affiliate->phone }}</td>
                        <td>{{ $affiliate->affiliate_code }}</td>
                        <td>{{ number_format((float) $affiliate->balance, 2) }}</td>
                        <td>{{ $affiliate->links_count }}</td>
                        <td>{{ $affiliate->commissions_count }}</td>
                        <td>{{ $affiliate->wallet_transactions_count }}</td>
                        <td>{{ ucfirst($affiliate->status) }}</td>
                        <td>
                            <form action="{{ route('admin.affiliates.impersonate', $affiliate) }}" method="POST" onsubmit="return confirm('Login as this affiliate?')">
                                @csrf
                                <button type="submit" class="button small blue">
                                    <span class="icon"><i class="mdi mdi-login"></i></span>
                                    <span>Login as Affiliate</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="has-text-centered">No affiliate found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-6">
                {{ $affiliates->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
