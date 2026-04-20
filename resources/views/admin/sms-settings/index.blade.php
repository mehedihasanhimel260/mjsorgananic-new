@extends('layouts.admin_main')

@section('content')
@php($activeTemplate = $templates->firstWhere('is_weekly_active', true))

<section class="section main-section" x-data="smsTemplatePicker()">
    <div class="mx-4 mb-6 overflow-hidden rounded-[28px] border border-emerald-100 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.22),_transparent_32%),linear-gradient(135deg,#0f766e_0%,#0f9f8f_38%,#1d4ed8_100%)] text-white shadow-[0_24px_80px_rgba(15,118,110,0.28)]">
        <div class="grid gap-6 px-6 py-7 lg:grid-cols-[1.45fr,1fr] lg:px-8 lg:py-8">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-emerald-100/90">SMS Delivery Hub</p>
                <h1 class="mt-3 text-2xl font-black leading-tight lg:text-4xl">Reliable weekly SMS campaigns with clear template control</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-emerald-50 lg:text-base">
                    Weekly SMS প্রতি Friday 10:00 AM-এ batch করে যাবে। একই user same week-এ দ্বিতীয়বার weekly SMS পাবে না, কিন্তু manual single SMS যেকোনো সময় পাঠানো যাবে।
                </p>
                <div class="mt-5 flex flex-wrap gap-3 text-sm">
                    <span class="rounded-full bg-white/15 px-4 py-2 font-semibold backdrop-blur">Batch size: 100 users</span>
                    <span class="rounded-full bg-white/15 px-4 py-2 font-semibold backdrop-blur">Retry-safe weekly pipeline</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-2xl border border-white/10 bg-white/12 p-4 backdrop-blur">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-emerald-100">Active Weekly Template</p>
                    <p class="mt-2 text-sm font-bold leading-6">{{ $activeTemplate?->title ?? 'No active template selected' }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/12 p-4 backdrop-blur">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-emerald-100">Reachable Users</p>
                    <p class="mt-2 text-3xl font-black">{{ number_format($userCount) }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/12 p-4 backdrop-blur">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-emerald-100">SMS Balance</p>
                    <p class="mt-2 text-3xl font-black">{{ number_format((float) $setting->current_balance, 2) }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/12 p-4 backdrop-blur">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-emerald-100">Latest Campaign</p>
                    <p class="mt-2 text-sm font-bold leading-6">{{ optional($campaigns->first())->title ?? 'No campaign run yet' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-4 grid grid-cols-1 gap-6 2xl:grid-cols-[1.25fr,0.75fr]">
        <div class="card overflow-hidden rounded-3xl border border-slate-200 shadow-sm">
            <header class="card-header border-b border-slate-100 bg-slate-50/70">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-cog-outline"></i></span>
                    Gateway Settings
                </p>
            </header>
            <div class="card-content">
                <form method="POST" action="{{ route('admin.sms-settings.update') }}">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
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
                                <div class="select is-fullwidth">
                                    <select name="transaction_type" required>
                                        @foreach (['T' => 'Transactional (T)', 'D' => 'Dynamic (D)', 'P' => 'Promotional (P)'] as $value => $label)
                                            <option value="{{ $value }}" @selected(old('transaction_type', $setting->transaction_type ?? 'T') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <button type="submit" class="button green">Update Settings</button>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                            Last balance check: {{ $setting->last_balance_checked_at ? $setting->last_balance_checked_at->format('Y-m-d H:i') : 'Never checked' }}
                        </span>
                    </div>
                </form>
            </div>
        </div>

        <div class="card overflow-hidden rounded-3xl border border-slate-200 shadow-sm">
            <header class="card-header border-b border-slate-100 bg-slate-50/70">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-rocket-launch-outline"></i></span>
                    Weekly Campaign
                </p>
            </header>
            <div class="card-content space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Currently Active</p>
                    <p class="mt-2 text-lg font-black text-slate-900">{{ $activeTemplate?->title ?? 'No active template selected' }}</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ $activeTemplate ? 'এই template-টাই weekly campaign-এ ব্যবহার হবে।' : 'Weekly send চালাতে আগে একটি template active করতে হবে।' }}
                    </p>
                </div>

                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Schedule</p>
                    <p class="mt-2 text-sm font-bold text-emerald-900">Every Friday at 10:00 AM</p>
                    <p class="mt-1 text-sm leading-6 text-emerald-700">Same week-এ same user দ্বিতীয়বার weekly SMS পাবে না।</p>
                </div>

                <form method="POST" action="{{ route('admin.sms-settings.refresh-balance') }}">
                    @csrf
                    <button type="submit" class="button blue w-full">
                        <span class="icon"><i class="mdi mdi-refresh"></i></span>
                        <span>Refresh Balance</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.sms-settings.send-bulk') }}">
                    @csrf
                    <button type="submit" class="button green w-full">Run Weekly Campaign Now</button>
                </form>
            </div>
        </div>
    </div>

    <div class="mx-4 mt-6 grid grid-cols-1 gap-6 xl:grid-cols-[0.95fr,1.05fr]">
        <div class="card overflow-hidden rounded-3xl border border-slate-200 shadow-sm">
            <header class="card-header border-b border-slate-100 bg-slate-50/70">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-text-box-plus-outline"></i></span>
                    Create Template
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
                            <textarea class="textarea" name="message" rows="7" placeholder="Write the SMS body here" required>{{ old('message') }}</textarea>
                        </div>
                    </div>
                    <label class="mb-4 inline-flex items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        <input type="checkbox" name="is_weekly_active" value="1" @checked(old('is_weekly_active'))>
                        <span>Save and make this the active weekly template</span>
                    </label>
                    <div>
                        <button type="submit" class="button green">Save Template</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card overflow-hidden rounded-3xl border border-slate-200 shadow-sm">
            <header class="card-header border-b border-slate-100 bg-slate-50/70">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-chart-timeline-variant"></i></span>
                    Weekly Campaign History
                </p>
            </header>
            <div class="card-content">
                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th>Week</th>
                                <th>Template</th>
                                <th>Status</th>
                                <th>Sent</th>
                                <th>Failed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaigns as $campaign)
                                <tr>
                                    <td>{{ $campaign->week_key }}</td>
                                    <td>{{ $campaign->title }}</td>
                                    <td>
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ in_array($campaign->status, ['completed']) ? 'bg-green-100 text-green-700' : (in_array($campaign->status, ['failed', 'partially_failed']) ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                            {{ ucfirst(str_replace('_', ' ', $campaign->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $campaign->sent_recipients }}/{{ $campaign->total_recipients }}</td>
                                    <td>{{ $campaign->failed_recipients }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="has-text-centered">No weekly campaign found yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-4 mt-6 card overflow-hidden rounded-3xl border border-slate-200 shadow-sm">
        <header class="card-header border-b border-slate-100 bg-slate-50/70">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-layers-triple-outline"></i></span>
                Template Library
            </p>
        </header>
        <div class="card-content">
            <div class="mb-5 grid gap-3 md:grid-cols-3">
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Active Template</p>
                    <p class="mt-2 text-base font-black text-emerald-900">{{ $activeTemplate?->title ?? 'Not selected' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Total Templates</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ $templates->count() }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Status Rule</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800">Only one template can stay active weekly at a time</p>
                </div>
            </div>

            <div class="space-y-4">
                @forelse($templates as $template)
                    <div class="rounded-[24px] border {{ $template->is_weekly_active ? 'border-emerald-200 bg-emerald-50/70 shadow-[0_12px_30px_rgba(16,185,129,0.08)]' : 'border-slate-200 bg-white' }} p-4 md:p-5">
                        <div class="grid gap-5 xl:grid-cols-[1.15fr,0.85fr]">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-black text-slate-900">{{ $template->title }}</h3>
                                    @if($template->is_weekly_active)
                                        <span class="inline-flex rounded-full bg-emerald-600 px-3 py-1 text-xs font-semibold text-white">Active Weekly</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Inactive</span>
                                    @endif
                                </div>
                                <div class="mt-4 rounded-2xl border border-slate-100 bg-white/70 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Message Preview</p>
                                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $template->message }}</p>
                                </div>
                            </div>

                            <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-4">
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Template Status</p>
                                        <p class="mt-1 text-sm font-bold {{ $template->is_weekly_active ? 'text-emerald-700' : 'text-slate-700' }}">
                                            {{ $template->is_weekly_active ? 'Currently selected for weekly campaign' : 'Not selected for weekly campaign' }}
                                        </p>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('admin.sms-settings.templates.update', $template) }}" class="space-y-3">
                                    @csrf
                                    @method('PATCH')
                                    <div class="field">
                                        <label class="label">Title</label>
                                        <div class="control">
                                            <input class="input" type="text" name="title" value="{{ $template->title }}" required>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label class="label">Message</label>
                                        <div class="control">
                                            <textarea class="textarea" name="message" rows="4" required>{{ $template->message }}</textarea>
                                        </div>
                                    </div>
                                    <label class="inline-flex items-center gap-3 text-sm text-slate-700">
                                        <input type="checkbox" name="is_weekly_active" value="1" @checked($template->is_weekly_active)>
                                        <span>Keep this as weekly active template</span>
                                    </label>
                                    <div class="flex flex-wrap gap-2 pt-2">
                                        @if($template->is_weekly_active)
                                            <span class="button green cursor-default">Active Weekly</span>
                                        @else
                                            <button type="submit" class="button green" formaction="{{ route('admin.sms-settings.templates.activate', $template) }}" formmethod="POST">Set Active Weekly</button>
                                        @endif
                                        <button type="button" class="button blue" @click="fillManual('single_message', @js($template->message))">Use For Single SMS</button>
                                        <button type="submit" class="button warning">Update</button>
                                    </div>
                                </form>

                                <form method="POST" action="{{ route('admin.sms-settings.templates.destroy', $template) }}" class="mt-3" onsubmit="return confirm('Delete this SMS template?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button red">Delete Template</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-slate-500">
                        No SMS template saved yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mx-4 mt-6 grid grid-cols-1 gap-6 xl:grid-cols-[0.9fr,1.1fr]">
        <div class="card overflow-hidden rounded-3xl border border-slate-200 shadow-sm">
            <header class="card-header border-b border-slate-100 bg-slate-50/70">
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
                                        <option value="{{ $template->id }}" data-message="{{ $template->message }}">{{ $template->title }}{{ $template->is_weekly_active ? ' - Active' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Message</label>
                        <div class="control">
                            <textarea class="textarea" name="single_message" rows="7" required>{{ old('single_message', $setting->last_single_message) }}</textarea>
                        </div>
                        <p class="help">Template select korle message auto-fill hobe, chaile edit korte parbe.</p>
                    </div>
                    <button type="submit" class="button blue w-full sm:w-auto">Queue Single SMS</button>
                </form>
            </div>
        </div>

        <div class="card overflow-hidden rounded-3xl border border-slate-200 shadow-sm">
            <header class="card-header border-b border-slate-100 bg-slate-50/70">
                <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="card-header-title !mb-0">
                        <span class="icon"><i class="mdi mdi-history"></i></span>
                        SMS History
                    </p>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}
                    </span>
                </div>
            </header>
            <div class="card-content">
                <div class="hidden lg:block overflow-x-auto rounded-2xl border border-slate-100">
                    <table>
                        <thead>
                            <tr>
                                <th>Phone</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Status Code</th>
                                <th>Status</th>
                                <th>Message</th>
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
                                    <td>{{ $log->sent_at?->format('Y-m-d H:i') ?? 'Queued' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="has-text-centered">No SMS history found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="space-y-3 lg:hidden">
                    @forelse($logs as $log)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-black text-slate-900">{{ $log->phone }}</p>
                                <span class="inline-flex rounded-full bg-slate-900 px-3 py-1 text-[11px] font-semibold text-white">{{ ucfirst($log->send_type) }}</span>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">User</p>
                                    <p class="mt-1 text-slate-700">{{ $log->user?->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Status Code</p>
                                    <p class="mt-1 text-slate-700">{{ $log->status_code ?? 'N/A' }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Status</p>
                                    <p class="mt-1 text-slate-700">{{ $log->status_text ?? 'Pending' }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Message</p>
                                    <p class="mt-1 leading-6 text-slate-700">{{ \Illuminate\Support\Str::limit($log->message, 120) }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Sent At</p>
                                    <p class="mt-1 text-slate-700">{{ $log->sent_at?->format('Y-m-d H:i') ?? 'Queued' }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-slate-500">
                            No SMS history found.
                        </div>
                    @endforelse
                </div>

                @if($logs->hasPages())
                    <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-4">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
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
