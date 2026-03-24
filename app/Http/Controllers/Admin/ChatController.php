<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        $chats = Chat::with(['user', 'latestConversion'])
            ->latest('last_message_at')
            ->latest()
            ->paginate(20);

        return view('admin.chats.index', compact('chats'));
    }

    public function show(Chat $chat)
    {
        $chat->load(['user', 'conversions.user', 'conversions.admin']);

        return view('admin.chats.show', compact('chat'));
    }

    public function reply(Request $request, Chat $chat)
    {
        $validated = $request->validate([
            'convertion_message' => 'required|string|max:5000',
        ]);

        $chat->conversions()->create([
            'user_id' => $chat->user_id,
            'admin_id' => auth('admin')->id(),
            'sender_type' => 'admin',
            'convertion_message' => $validated['convertion_message'],
        ]);

        $chat->update([
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        return redirect()->route('admin.chats.show', $chat->id)->with('success', 'Reply sent successfully.');
    }
}
