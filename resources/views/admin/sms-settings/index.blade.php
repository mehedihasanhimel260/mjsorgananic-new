@extends('layouts.admin_main')

@section('content')
<section class="section main-section" x-data="smsTemplatePicker()">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-message-settings-outline"></i></span>
                    SMS Marketing Settings
                </p>
            </header>
            <div class="card-content">
                <form method="POST" action="{{ route('admin.sms-settings.update') }}">
                    @csrf
                    <div class="field">
                        <label class="label">Username</label>
                        <div class="control">
                            <input class="input" type="text" name="username" value="{{ old('username', $setting->username) }}" required>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Sender Name</label>
                        <div class="control">
                            <input class="input" type="text" name="sender_id" value="{{ old('sender_id', $setting->sender_id) }}" required>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">API Key</label>
                        <div class="control">
                            <input class="input" type="text" name="api_key" value="{{ old('api_key', $setting->api_key) }}" required>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Transaction Type</label>
                        <div class="control">
                            <div class="select">
                                <select name="transaction_type" required>
                                    @foreach (['T' => 'Transactional (T)', 'D' => 'Dynamic (D)', 'P' => 'Promotional (P)'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('transaction_type', $setting->transaction_type ?? 'T') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="button green">Update Settings</button>
                </form>
            </div>
        </div>

        <div class="card has-table">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-wallet-outline"></i></span>
                    SMS Balance
                </p>
                <form method="POST" action="{{ route('admin.sms-settings.refresh-balance') }}">
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
                            <th>Last Checked</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ number_format((float) $setting->current_balance, 2) }}</td>
                            <td>{{ $setting->last_balance_checked_at ? $setting->last_balance_checked_at->format('Y-m-d H:i') : 'Never checked' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-6">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-text-box-plus-outline"></i></span>
                    Create SMS Template
                </p>
            </header>
            <div class="card-content">
                <form method="POST" action="{{ route('admin.sms-settings.templates.store') }}">
                    @csrf
                    <div class="field">
                        <label class="label">Template Title</label>
                        <div class="control">
                            <input class="input" type="text" name="title" value="{{ old('title') }}" placeholder="Order follow-up, Eid offer, etc." required>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Message</label>
                        <div class="control">
                            <textarea class="textarea" name="message" rows="6" placeholder="Write the SMS body here" required>{{ old('message') }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="button green">Save Template</button>
                </form>
            </div>
        </div>

        <div class="card has-table">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-content-save-edit-outline"></i></span>
                    Saved Templates
                </p>
            </header>
            <div class="card-content">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td colspan="3">
                                    <form method="POST" action="{{ route('admin.sms-settings.templates.update', $template) }}" class="space-y-3">
                                        @csrf
                                        @method('PATCH')
                                        <div class="grid grid-cols-1 gap-3 xl:grid-cols-[220px,1fr,160px]">
                                            <input class="input" type="text" name="title" value="{{ old('title_'.$template->id, $template->title) }}" required>
                                            <textarea class="textarea" name="message" rows="3" required>{{ old('message_'.$template->id, $template->message) }}</textarea>
                                            <div class="flex flex-col gap-2 sm:flex-row xl:flex-col">
                                                <button type="button" class="button blue" @click="fillManual('bulk_message', @js($template->message)); fillManual('single_message', @js($template->message))">Use Now</button>
                                                <button type="submit" class="button green">Update</button>
                                            </div>
                                        </div>
                                    </form>
                                    <form method="POST" action="{{ route('admin.sms-settings.templates.destroy', $template) }}" class="mt-2" onsubmit="return confirm('Delete this SMS template?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="button red">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="has-text-centered">No SMS template saved yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-6">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-account-multiple-outline"></i></span>
                    All User SMS
                </p>
            </header>
            <div class="card-content">
                <p class="mb-4 text-sm text-gray-600">This will queue SMS for all users with valid phone numbers. Total users with phone: <strong>{{ $userCount }}</strong></p>
                <form method="POST" action="{{ route('admin.sms-settings.send-bulk') }}">
                    @csrf
                    <div class="field">
                        <label class="label">Choose Template</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="bulk_template_id" @change="fillFromSelect($event, 'bulk_message')">
                                    <option value="">Write custom message</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}" data-message="{{ $template->message }}" @selected((string) old('bulk_template_id') === (string) $template->id)>{{ $template->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Message</label>
                        <div class="control">
                            <textarea class="textarea" name="bulk_message" rows="6" required>{{ old('bulk_message', $setting->last_bulk_message) }}</textarea>
                        </div>
                        <p class="help">Template select ???? message auto-fill ???, ????? edit ???? ?????.</p>
                    </div>
                    <button type="submit" class="button green">Queue All User SMS</button>
                </form>
            </div>
        </div>

        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-account-outline"></i></span>
                    Single SMS
                </p>
            </header>
            <div class="card-content">
                <form method="POST" action="{{ route('admin.sms-settings.send-single') }}">
                    @csrf
                    <div class="field">
                        <label class="label">Phone Number</label>
                        <div class="control">
                            <input class="input" type="text" name="phone" value="{{ old('phone') }}" placeholder="01XXXXXXXXX or 8801XXXXXXXXX" required>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Choose Template</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="single_template_id" @change="fillFromSelect($event, 'single_message')">
                                    <option value="">Write custom message</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}" data-message="{{ $template->message }}" @selected((string) old('single_template_id') === (string) $template->id)>{{ $template->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Message</label>
                        <div class="control">
                            <textarea class="textarea" name="single_message" rows="6" required>{{ old('single_message', $setting->last_single_message) }}</textarea>
                        </div>
                        <p class="help">Template select ???? message auto-fill ???, ????? edit ???? ?????.</p>
                    </div>
                    <button type="submit" class="button blue">Queue Single SMS</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card has-table mt-6">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-history"></i></span>
                SMS History
            </p>
        </header>
        <div class="card-content">
            <table>
                <thead>
                    <tr>
                        <th>Phone</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Status Code</th>
                        <th>Status</th>
                        <th>Message</th>
                        <th>Gateway Response</th>
                        <th>Sent At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->phone }}</td>
                        <td>{{ $log->user?->name ?? 'N/A' }}</td>
                        <td>{{ ucfirst($log->send_type) }}</td>
                        <td>{{ $log->status_code ?? 'N/A' }}</td>
                        <td>{{ $log->status_text ?? 'Pending' }}</td>
                        <td class="text-sm">{{ \Illuminate\Support\Str::limit($log->message, 80) }}</td>
                        <td class="text-sm">{{ \Illuminate\Support\Str::limit($log->gateway_response, 100) }}</td>
                        <td>{{ $log->sent_at?->format('Y-m-d H:i') ?? 'Queued' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="has-text-centered">No SMS history found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function smsTemplatePicker() {
    return {
        fillFromSelect(event, targetName) {
            const selected = event.target.options[event.target.selectedIndex];
            const message = selected?.dataset?.message || '';
            this.fillManual(targetName, message);
        },
        fillManual(targetName, message) {
            const field = document.querySelector(`[name="${targetName}"]`);
            if (field) {
                field.value = message || '';
            }
        }
    };
}
</script>
@endpush
