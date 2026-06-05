<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateFacebookAiReplyJob;
use App\Models\FbSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FbWebhookController extends Controller
{
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

                if (! $senderPsid || $messageText === '' || isset($event['message']['is_echo'])) {
                    continue;
                }

                try {
                    $user = User::firstOrCreate(
                        ['PSID_OF_USER' => $senderPsid],
                        [
                            'name' => 'Facebook User',
                            'phone' => 'fb-'.$senderPsid,
                            'password' => bcrypt('fb-user-'.uniqid()),
                        ]
                    );

                    GenerateFacebookAiReplyJob::dispatch($user->id, $senderPsid, $messageText);
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
