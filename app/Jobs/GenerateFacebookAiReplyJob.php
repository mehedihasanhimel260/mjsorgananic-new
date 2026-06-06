<?php

namespace App\Jobs;

use App\Models\Chat;
use App\Models\Conversion;
use App\Models\User;
use App\Services\AiReplyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateFacebookAiReplyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;

    public string $senderPsid;

    public string $message;

    public ?int $chatId = null;

    public function __construct(int $userId, string $senderPsid, string $message, ?int $chatId = null)
    {
        $this->userId = $userId;
        $this->senderPsid = $senderPsid;
        $this->message = $message;
        $this->chatId = $chatId;
    }

    public function handle(AiReplyService $aiReplyService): void
    {
        $user = User::find($this->userId);
        $chat = $this->chatId ? Chat::find($this->chatId) : Chat::where('user_id', $this->userId)->latest('id')->first();

        if (! $user || ! $chat) {
            Log::warning('Facebook AI reply job aborted: user not found.', [
                'user_id' => $this->userId,
                'chat_id' => $this->chatId,
                'sender_psid' => $this->senderPsid,
            ]);

            return;
        }

        try {
            $conversationHistory = $chat->conversions()
                ->latest()
                ->take(15)
                ->get()
                ->reverse()
                ->map(fn (Conversion $conversion) => [
                    'sender_type' => $conversion->sender_type,
                    'message' => $conversion->convertion_message,
                ])
                ->values()
                ->all();

            $replyMessage = $aiReplyService->generateReply($user, $this->message, $conversationHistory);

            $alreadyReplied = Conversion::where('chat_id', $chat->id)
                ->where('sender_type', 'ai')
                ->where('convertion_message', $replyMessage)
                ->where('created_at', '>=', now()->subMinutes(5))
                ->exists();

            if ($alreadyReplied) {
                return;
            }

            $chat->conversions()->create([
                'user_id' => $user->id,
                'sender_type' => 'ai',
                'convertion_message' => $replyMessage,
            ]);

            $chat->update([
                'status' => 'open',
                'last_message_at' => now(),
            ]);

            fb_send_page_message($this->senderPsid, $replyMessage);
        } catch (\Throwable $exception) {
            Log::error('Queued Facebook AI reply failed.', [
                'user_id' => $this->userId,
                'chat_id' => $chat->id,
                'sender_psid' => $this->senderPsid,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
