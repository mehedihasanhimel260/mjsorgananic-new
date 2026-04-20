@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="mx-4 mb-6 overflow-hidden rounded-[28px] border border-sky-100 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.24),_transparent_35%),linear-gradient(135deg,#0f172a_0%,#1d4ed8_55%,#0ea5e9_100%)] text-white shadow-[0_24px_80px_rgba(29,78,216,0.22)]">
        <div class="grid gap-6 px-6 py-7 lg:grid-cols-[1.4fr,1fr] lg:px-8">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-100/90">User Directory</p>
                <h1 class="mt-3 text-2xl font-black leading-tight lg:text-4xl">Search users fast by phone number and manage records cleanly</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-sky-50 lg:text-base">
                    4 digit-er beshi phone number search দিলে matching users instantly list হবে। Desktop-এ full table, mobile-এ readable responsive layout থাকবে।
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-2xl border border-white/10 bg-white/12 p-4 backdrop-blur">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-sky-100">Visible Users</p>
                    <p class="mt-2 text-3xl font-black">{{ number_format($users->total()) }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/12 p-4 backdrop-blur">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-sky-100">Current Page</p>
                    <p class="mt-2 text-3xl font-black">{{ $users->currentPage() }}</p>
                </div>
                <div class="col-span-2 rounded-2xl border border-white/10 bg-white/12 p-4 backdrop-blur">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-sky-100">Search State</p>
                    <p class="mt-2 text-sm font-bold leading-6">{{ request('phone_search') ? 'Filtered by: '.request('phone_search') : 'Showing latest users list' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-4 card overflow-hidden rounded-3xl border border-slate-200 shadow-sm">
        <header class="card-header border-b border-slate-100 bg-slate-50/80">
            <div class="flex w-full flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <p class="card-header-title !mb-0">
                    <span class="icon"><i class="mdi mdi-account-group"></i></span>
                    Users List
                </p>
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        Page {{ $users->currentPage() }} of {{ $users->lastPage() }}
                    </span>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        {{ $users->count() }} shown
                    </span>
                </div>
            </div>
        </header>

        <div class="card-content">
            <div class="mb-6 rounded-[24px] border border-slate-200 bg-slate-50 p-4 md:p-5">
                <form method="GET" action="{{ route('admin.users.index') }}" class="grid gap-4 lg:grid-cols-[1fr,auto] lg:items-end">
                    <div class="grid gap-4 md:grid-cols-[1fr,220px]">
                        <div class="field mb-0">
                            <label class="label">Search by phone digits</label>
                            <div class="control">
                                <input class="input" type="text" name="phone_search" value="{{ request('phone_search', $search) }}" placeholder="Type more than 4 digits, e.g. 01712 or 66127">
                            </div>
                            <p class="help">Phone number-এর 4 digit-er বেশি লিখলে partial match করে user show হবে।</p>
                        </div>
                        <div class="rounded-2xl border border-sky-100 bg-sky-50 px-4 py-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-600">Quick Tip</p>
                            <p class="mt-2 text-sm leading-6 text-sky-800">Example: `01712`, `66127`, `8801`</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="button green">Search User</button>
                        <a href="{{ route('admin.users.index') }}" class="button blue">
                            <span class="icon"><i class="mdi mdi-refresh"></i></span>
                            <span>Reset</span>
                        </a>
                    </div>
                </form>
            </div>

            <div class="hidden lg:block overflow-x-auto rounded-[24px] border border-slate-100">
                <table>
                    <thead>
                        <tr>
                            <th>SI</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>IP Address</th>
                            <th>Saved Address</th>
                            <th>Created</th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                        <tr>
                            <td>{{ $users->firstItem() + $loop->index }}</td>
                            <td data-label="Name">{{ $user->name }}</td>
                            <td data-label="Phone">
                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                    {{ $user->phone }}
                                </span>
                            </td>
                            <td data-label="Email">{{ $user->email ?? 'N/A' }}</td>
                            <td data-label="IP Address">{{ $user->ip_address ?? 'N/A' }}</td>
                            <td data-label="Saved Address">{{ $user->saved_address ?? 'N/A' }}</td>
                            <td data-label="Created">
                                <small class="text-gray-500" title="{{ $user->created_at }}">{{ $user->created_at?->format('Y-m-d') }}</small>
                            </td>
                            <td class="actions-cell">
                                <div class="buttons right nowrap">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="button small blue" type="button">
                                        <span class="icon"><i class="mdi mdi-pencil"></i></span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="has-text-centered">No users found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="space-y-3 lg:hidden">
                @forelse ($users as $user)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-lg font-black text-slate-900">{{ $user->name }}</p>
                                <p class="mt-1 inline-flex rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">{{ $user->phone }}</p>
                            </div>
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="button small blue" type="button">
                                <span class="icon"><i class="mdi mdi-pencil"></i></span>
                            </a>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="col-span-2">
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Email</p>
                                <p class="mt-1 text-slate-700">{{ $user->email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">IP Address</p>
                                <p class="mt-1 text-slate-700">{{ $user->ip_address ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Created</p>
                                <p class="mt-1 text-slate-700">{{ $user->created_at?->format('Y-m-d') }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Saved Address</p>
                                <p class="mt-1 leading-6 text-slate-700">{{ $user->saved_address ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-slate-500">
                        No users found.
                    </div>
                @endforelse
            </div>

            <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
