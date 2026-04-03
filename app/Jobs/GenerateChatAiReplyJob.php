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

class GenerateChatAiReplyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $chatId,
        public int $userId,
        public string $message,
    ) {
    }

    public function handle(AiReplyService $aiReplyService): void
    {
        $chat = Chat::find($this->chatId);
        $user = User::find($this->userId);

        if (! $chat || ! $user) {
            return;
        }

        try {
            $replyMessage = $aiReplyService->generateReply($user, $this->message);

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
        } catch (\Throwable $exception) {
            Log::error('Queued chat AI reply failed.', [
                'chat_id' => $this->chatId,
                'user_id' => $this->userId,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
