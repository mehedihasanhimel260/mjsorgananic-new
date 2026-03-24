@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card has-table">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-chat-processing-outline"></i></span>
                Chat Tickets
            </p>
            <a href="{{ route('admin.chats.index') }}" class="button blue">
                <span class="icon"><i class="mdi mdi-refresh"></i></span>
                <span>Refresh</span>
            </a>
        </header>
        <div class="card-content">
            <table>
                <thead>
                    <tr>
                        <th>SI</th>
                        <th>Ticket</th>
                        <th>User Name</th>
                        <th>Phone</th>
                        <th>Last Message</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($chats as $chat)
                    <tr>
                        <td>{{ $chats->firstItem() + $loop->index }}</td>
                        <td data-label="Ticket">{{ $chat->ticket_number }}</td>
                        <td data-label="User Name">{{ $chat->user?->name ?? 'N/A' }}</td>
                        <td data-label="Phone">{{ $chat->user?->phone ?? 'N/A' }}</td>
                        <td data-label="Last Message">{{ \Illuminate\Support\Str::limit($chat->latestConversion?->convertion_message ?? 'No message yet', 60) }}</td>
                        <td data-label="Status">{{ ucfirst($chat->status) }}</td>
                        <td data-label="Updated">{{ optional($chat->last_message_at)->format('Y-m-d H:i') ?? $chat->updated_at->format('Y-m-d H:i') }}</td>
                        <td class="actions-cell">
                            <div class="buttons right nowrap">
                                <a href="{{ route('admin.chats.show', $chat->id) }}" class="button small blue" type="button">
                                    <span class="icon"><i class="mdi mdi-eye"></i></span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="has-text-centered">No chat ticket found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">
                {{ $chats->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
