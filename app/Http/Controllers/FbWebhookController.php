<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateFacebookAiReplyJob;
use App\Models\Chat;
use App\Models\Conversion;
use App\Models\FbSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FbWebhookController extends Controller
{
    private function findOrCreateChat(User $user): Chat
    {
        $chat = Chat::where('user_id', $user->id)->latest('id')->first();

        if ($chat) {
            return $chat;
        }

        return Chat::create([
            'user_id' => $user->id,
            'ticket_number' => 'FB-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => 'open',
            'last_message_at' => now(),
        ]);
    }

    public function verify(Request $request)
    {
        $verifyToken = FbSetting::query()->value('verify_token') ?: env('FB_VERIFY_TOKEN', 'mjs-organic-webhook');

        if (
            $request->input('hub_mode') === 'subscribe' &&
            $request->input('hub_verify_token') === $verifyToken
        ) {
            return response($request->input('hub_challenge'), 200);
        }

        return response('Invalid verify token.', 403);
    }

    public function receive(Request $request)
    {
        $payload = $request->all();

        foreach (($payload['entry'] ?? []) as $entry) {
            foreach (($entry['messaging'] ?? []) as $event) {
                $senderPsid = $event['sender']['id'] ?? null;
                $messageText = trim((string) ($event['message']['text'] ?? ''));
                $externalMessageId = $event['message']['mid'] ?? null;

                if (! $senderPsid || $messageText === '' || isset($event['message']['is_echo'])) {
                    continue;
                }

                try {
                    $payload = DB::transaction(function () use ($senderPsid, $messageText, $externalMessageId) {
                        $user = User::firstOrCreate(
                            ['PSID_OF_USER' => $senderPsid],
                            [
                                'name' => 'Facebook User',
                                'phone' => 'fb-'.$senderPsid,
                                'password' => bcrypt('fb-user-'.uniqid()),
                            ]
                        );

                        $chat = $this->findOrCreateChat($user);

                        if ($externalMessageId && Conversion::where('external_message_id', $externalMessageId)->exists()) {
                            return null;
                        }

                        if (! $externalMessageId) {
                            $recentDuplicate = Conversion::query()
                                ->where('chat_id', $chat->id)
                                ->where('sender_type', 'user')
                                ->where('convertion_message', $messageText)
                                ->where('created_at', '>=', now()->subMinute())
                                ->exists();

                            if ($recentDuplicate) {
                                return null;
                            }
                        }

                        $chat->conversions()->create([
                            'user_id' => $user->id,
                            'sender_type' => 'user',
                            'external_message_id' => $externalMessageId,
                            'convertion_message' => $messageText,
                        ]);

                        $chat->update([
                            'status' => 'open',
                            'last_message_at' => now(),
                        ]);

                        return [
                            'chat_id' => $chat->id,
                            'user_id' => $user->id,
                        ];
                    });

                    if (! $payload) {
                        continue;
                    }

                    GenerateFacebookAiReplyJob::dispatch($payload['user_id'], $senderPsid, $messageText, $payload['chat_id']);
                } catch (\Throwable $exception) {
                    Log::error('Facebook webhook message handling failed.', [
                        'message' => $exception->getMessage(),
                        'payload' => $event,
                    ]);
                }
            }
        }

        return response()->json(['status' => 'EVENT_RECEIVED']);
    }
}
