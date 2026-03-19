@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-account-group"></i></span>
                Users List
            </p>
            <a href="{{ route('admin.users.index') }}" class="button blue">
                <span class="icon"><i class="mdi mdi-refresh"></i></span>
                <span>Refresh</span>
            </a>
        </header>
        <div class="card-content">
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
                        <td data-label="Phone">{{ $user->phone }}</td>
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
            <div class="table-pagination mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
