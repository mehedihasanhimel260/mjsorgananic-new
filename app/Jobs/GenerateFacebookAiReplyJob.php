<?php

namespace App\Jobs;

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

    public function __construct(
        public int $userId,
        public string $senderPsid,
        public string $message,
    ) {
    }

    public function handle(AiReplyService $aiReplyService): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        try {
            $replyMessage = $aiReplyService->generateReply($user, $this->message);
            fb_send_page_message($this->senderPsid, $replyMessage);
        } catch (\Throwable $exception) {
            Log::error('Queued Facebook AI reply failed.', [
                'user_id' => $this->userId,
                'sender_psid' => $this->senderPsid,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
