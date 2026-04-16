<?php

namespace App\Jobs;

use App\Models\SmsLog;
use App\Services\SmsGatewayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBulkSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param array<int, array{phone:string,user_id:int|null}> $recipients
     */
    public function __construct(
        public array $recipients,
        public string $message,
        public ?int $sentByAdminId,
    ) {
    }

    public function handle(SmsGatewayService $smsGatewayService): void
    {
        foreach ($this->recipients as $recipient) {
            $result = $smsGatewayService->sendSms($recipient['phone'], $this->message);

            SmsLog::create([
                'user_id' => $recipient['user_id'],
                'sent_by_admin_id' => $this->sentByAdminId,
                'phone' => $result['phone'] ?? $recipient['phone'],
                'message' => $this->message,
                'send_type' => 'bulk',
                'status_code' => $result['code'],
                'status_text' => $result['status_text'],
                'gateway_response' => $result['raw_response'],
                'sent_at' => now(),
            ]);
        }
    }
}
