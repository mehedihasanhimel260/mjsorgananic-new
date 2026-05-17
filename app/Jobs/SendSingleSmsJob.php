<?php

namespace App\Jobs;

use App\Models\SmsLog;
use App\Services\SmsGatewayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSingleSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $phone,
        public string $message,
        public ?int $userId,
        public ?int $sentByAdminId,
    ) {}

    public function handle(SmsGatewayService $smsGatewayService): void
    {
        $result = $smsGatewayService->sendSms($this->phone, $this->message);

        SmsLog::create([
            'user_id' => $this->userId,
            'sent_by_admin_id' => $this->sentByAdminId,
            'phone' => $result['phone'] ?? $this->phone,
            'message' => $this->message,
            'send_type' => 'single',
            'gateway_transaction_id' => $result['transaction_id'] ?? null,
            'status_code' => $result['code'],
            'status_text' => $result['status_text'],
            'delivery_status' => $result['success'] ? 'processing' : 'failed',
            'delivery_finalized_at' => $result['success'] ? null : now(),
            'gateway_response' => $result['raw_response'],
            'sent_at' => now(),
        ]);
    }
}
