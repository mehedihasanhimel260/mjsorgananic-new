<?php

namespace App\Http\Controllers\Front_site;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateChatAiReplyJob;
use App\Models\Chat;
use App\Models\Conversion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    private function normalizeBangladeshPhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '');

        if (! $digits) {
            return null;
        }

        if (strlen($digits) >= 11) {
            $digits = substr($digits, -11);
        }

        if (strlen($digits) !== 11 || ! str_starts_with($digits, '01')) {
            return null;
        }

        return $digits;
    }

    private function formatChat(Chat $chat): array
    {
        $chat->loadMissing(['user', 'conversions.admin', 'conversions.user']);

        return [
            'chat' => [
                'id' => $chat->id,
                'ticket_number' => $chat->ticket_number,
                'status' => $chat->status,
                'user_name' => $chat->user?->name,
                'user_phone' => $chat->user?->phone,
            ],
            'messages' => $chat->conversions->map(function (Conversion $conversion) {
                return [
                    'id' => $conversion->id,
                    'text' => $conversion->convertion_message,
                    'type' => in_array($conversion->sender_type, ['admin', 'ai'], true) ? 'guest' : 'user',
                    'sender_name' => match ($conversion->sender_type) {
                        'admin' => $conversion->admin?->name ?? 'Admin',
                        'ai' => 'AI Assistant',
                        default => $conversion->user?->name ?? 'User',
                    },
                    'created_at' => $conversion->created_at?->format('Y-m-d H:i'),
                ];
            })->values(),
        ];
    }

    private function findOrCreateChat(User $user): Chat
    {
        $chat = Chat::where('user_id', $user->id)->latest('id')->first();

        if ($chat) {
            return $chat;
        }

        return Chat::create([
            'user_id' => $user->id,
            'ticket_number' => 'CHAT-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => 'open',
            'last_message_at' => now(),
        ]);
    }

    public function history(Request $request)
    {
        $userId = $request->session()->get('user_id');

        if (! $userId) {
            return response()->json([
                'success' => true,
                'registered' => false,
                'messages' => [],
            ]);
        }

        $user = User::find($userId);

        if (! $user) {
            return response()->json([
                'success' => true,
                'registered' => false,
                'messages' => [],
            ]);
        }

        $chat = Chat::with(['user', 'conversions.user', 'conversions.admin'])
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        if (! $chat) {
            return response()->json([
                'success' => true,
                'registered' => true,
                'user' => [
                    'name' => $user->name,
                    'phone' => $user->phone,
                ],
                'messages' => [],
            ]);
        }

        return response()->json(array_merge([
            'success' => true,
            'registered' => true,
            'user' => [
                'name' => $user->name,
                'phone' => $user->phone,
            ],
        ], $this->formatChat($chat)));
    }

    public function storeMessage(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'required|string|max:5000',
        ]);

        try {
            $payload = DB::transaction(function () use ($request, $validated) {
                $normalizedPhone = $this->normalizeBangladeshPhone($validated['phone']);

                if (! $normalizedPhone) {
                    throw new \InvalidArgumentException('Please enter a valid 11 digit phone number.');
                }

                $user = User::where('phone', $normalizedPhone)->first();

                if ($user) {
                    $user->update([
                        'name' => $validated['name'],
                        'ip_address' => $request->ip(),
                        'last_user_agent' => substr((string) $request->userAgent(), 0, 65535),
                        'last_visit_at' => now(),
                        'last_logged_at' => now(),
                    ]);
                } else {
                    $user = User::create([
                        'name' => $validated['name'],
                        'phone' => $normalizedPhone,
                        'password' => Hash::make('chat-user-'.uniqid()),
                        'ip_address' => $request->ip(),
                        'last_user_agent' => substr((string) $request->userAgent(), 0, 65535),
                        'last_visit_at' => now(),
                        'last_logged_at' => now(),
                    ]);
                }

                $chat = $this->findOrCreateChat($user);

                $chat->conversions()->create([
                    'user_id' => $user->id,
                    'sender_type' => 'user',
                    'convertion_message' => $validated['message'],
                ]);

                $chat->update([
                    'status' => 'open',
                    'last_message_at' => now(),
                ]);

                $request->session()->put([
                    'user_id' => $user->id,
                    'customer_name' => $user->name,
                    'customer_phone' => $user->phone,
                ]);

                return [
                    'chat' => $chat->fresh(['user', 'conversions.user', 'conversions.admin']),
                    'user_id' => $user->id,
                    'message' => $validated['message'],
                ];
            });

            GenerateChatAiReplyJob::dispatch($payload['chat']->id, $payload['user_id'], $payload['message']);

            return response()->json(array_merge([
                'success' => true,
                'message' => 'Message sent successfully. AI reply is being prepared.',
            ], $this->formatChat($payload['chat'])));
        } catch (\Throwable $exception) {
            Log::error('Chat message store failed.', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'phone' => $validated['phone'],
            ]);

            return response()->json([
                'success' => false,
                'message' => $exception instanceof \InvalidArgumentException ? $exception->getMessage() : 'Chat message save failed.',
            ], $exception instanceof \InvalidArgumentException ? 422 : 500);
        }
    }
}
