@extends('layouts.admin_main')

@section('content')
<section class="section main-section">
    <div class="card mb-6">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-ticket-outline"></i></span>
                Chat Ticket Details
            </p>
            <a href="{{ route('admin.chats.index') }}" class="button blue">
                <span class="icon"><i class="mdi mdi-arrow-left"></i></span>
                <span>Back</span>
            </a>
        </header>
        <div class="card-content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="title is-5 mb-4">Ticket Information</h3>
                    <p><strong>Ticket:</strong> {{ $chat->ticket_number }}</p>
                    <p><strong>Status:</strong> {{ ucfirst($chat->status) }}</p>
                    <p><strong>Last Message:</strong> {{ optional($chat->last_message_at)->format('Y-m-d H:i') ?? 'N/A' }}</p>
                    <p><strong>Created:</strong> {{ $chat->created_at->format('Y-m-d H:i') }}</p>
                </div>
                <div>
                    <h3 class="title is-5 mb-4">User Information</h3>
                    <p><strong>Name:</strong> {{ $chat->user?->name ?? 'N/A' }}</p>
                    <p><strong>Phone:</strong> {{ $chat->user?->phone ?? 'N/A' }}</p>
                    <p><strong>Address:</strong> {{ $chat->user?->saved_address ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-message-text-outline"></i></span>
                Conversation
            </p>
        </header>
        <div class="card-content">
            <div class="max-h-[520px] space-y-4 overflow-y-auto pr-2">
                @forelse ($chat->conversions as $conversion)
                <div class="rounded-lg border px-4 py-3 {{ $conversion->sender_type === 'admin' ? 'bg-blue-50 border-blue-200 ml-10' : 'bg-gray-50 border-gray-200 mr-10' }}">
                    <div class="flex items-center justify-between gap-4">
                        <strong>{{ $conversion->sender_type === 'admin' ? ($conversion->admin?->name ?? 'Admin') : ($conversion->sender_type === 'ai' ? 'AI Assistant' : ($conversion->user?->name ?? 'User')) }}</strong>
                        <small>{{ $conversion->created_at->format('Y-m-d H:i') }}</small>
                    </div>
                    <p class="mt-2 text-gray-700">{{ $conversion->convertion_message }}</p>
                </div>
                @empty
                <div class="text-center text-gray-500">No conversation found.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="card mb-6">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-reply-outline"></i></span>
                Reply
            </p>
        </header>
        <div class="card-content">
            <form action="{{ route('admin.chats.reply', $chat->id) }}" method="POST">
                @csrf
                <div class="field">
                    <label class="label">Message</label>
                    <div class="control">
                        <textarea name="convertion_message" class="textarea" rows="4" placeholder="Write your reply..." required>{{ old('convertion_message') }}</textarea>
                    </div>
                </div>
                <div class="field">
                    <div class="control">
                        <button type="submit" class="button green">
                            <span class="icon"><i class="mdi mdi-send"></i></span>
                            <span>Send Reply</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
