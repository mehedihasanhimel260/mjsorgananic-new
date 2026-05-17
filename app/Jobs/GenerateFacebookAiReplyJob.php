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
    ) {}

    public function handle(AiReplyService $aiReplyService): void
    {
        info('Facebook AI reply job started.', [
            'user_id' => $this->userId,
            'sender_psid' => $this->senderPsid,
            'message_preview' => mb_substr($this->message, 0, 120),
        ]);

        $user = User::find($this->userId);

        if (! $user) {
            Log::warning('Facebook AI reply job aborted: user not found.', [
                'user_id' => $this->userId,
                'sender_psid' => $this->senderPsid,
            ]);

            return;
        }

        try {
            $replyMessage = $aiReplyService->generateReply($user, $this->message);
            info('Facebook AI reply generated.', [
                'user_id' => $this->userId,
                'sender_psid' => $this->senderPsid,
                'reply_preview' => mb_substr($replyMessage, 0, 120),
            ]);

            $sendResult = fb_send_page_message($this->senderPsid, $replyMessage);

            info('Facebook AI reply send attempt completed.', [
                'user_id' => $this->userId,
                'sender_psid' => $this->senderPsid,
                'send_result' => $sendResult,
            ]);
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
