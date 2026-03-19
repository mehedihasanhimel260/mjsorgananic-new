@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-key-variant"></i></span>
                    Steadfast API Settings
                </p>
            </header>
            <div class="card-content">
                <form method="POST" action="{{ route('admin.steadfast.update') }}">
                    @csrf
                    <div class="field">
                        <label class="label">Api-Key</label>
                        <div class="control">
                            <input class="input" type="text" name="api_key" value="{{ old('api_key', $setting->api_key) }}" placeholder="Enter Api-Key" required>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Secret-Key</label>
                        <div class="control">
                            <input class="input" type="text" name="secret_key" value="{{ old('secret_key', $setting->secret_key) }}" placeholder="Enter Secret-Key" required>
                        </div>
                    </div>
                    <hr>
                    <div class="field grouped">
                        <div class="control">
                            <button type="submit" class="button green">
                                Update Credentials
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card has-table">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-wallet-outline"></i></span>
                    Current Balance
                </p>
                <form method="POST" action="{{ route('admin.steadfast.refresh-balance') }}">
                    @csrf
                    <button type="submit" class="button blue">
                        <span class="icon"><i class="mdi mdi-refresh"></i></span>
                        <span>Refresh</span>
                    </button>
                </form>
            </header>
            <div class="card-content">
                <table>
                    <thead>
                        <tr>
                            <th>Current Balance</th>
                            <th>Last Synced</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td data-label="Current Balance">{{ number_format((float) $setting->current_balance, 2) }}</td>
                            <td data-label="Last Synced">
                                {{ $setting->last_balance_synced_at ? $setting->last_balance_synced_at->format('Y-m-d H:i') : 'Never synced' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
