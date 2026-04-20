<?php

namespace App\Services;

use App\Models\SmsSetting;
use Illuminate\Support\Facades\Http;

class SmsGatewayService
{
    private const SEND_URL = 'https://api.mimsms.com/api/SmsSending/SMS';
    private const BULK_SEND_URL = 'https://api.mimsms.com/api/SmsSending/OneToMany';
    private const BALANCE_URL = 'https://api.mimsms.com/api/SmsSending/balanceCheck';

    public function getSetting(): SmsSetting
    {
        return SmsSetting::query()->firstOrCreate([], [
            'username' => '',
            'sender_id' => '',
            'api_key' => '',
            'transaction_type' => 'T',
            'current_balance' => 0,
            'last_balance_checked_at' => null,
        ]);
    }

    public function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '880') && strlen($digits) >= 13) {
            $digits = substr($digits, 0, 13);
        } elseif (str_starts_with($digits, '01') && strlen($digits) >= 11) {
            $digits = '88'.substr($digits, 0, 11);
        } else {
            $lastEleven = substr($digits, -11);
            if (strlen($lastEleven) === 11 && str_starts_with($lastEleven, '01')) {
                $digits = '88'.$lastEleven;
            }
        }

        if (! preg_match('/^8801\d{9}$/', $digits)) {
            return null;
        }

        return $digits;
    }

    public function refreshBalance(): array
    {
        $setting = $this->getSetting();

        if (blank($setting->username) || blank($setting->api_key)) {
            return [
                'success' => false,
                'message' => 'Username and API Key are required before checking balance.',
            ];
        }

        $response = Http::asJson()
            ->timeout(30)
            ->withoutVerifying()
            ->post(self::BALANCE_URL, [
                'UserName' => $setting->username,
                'Apikey' => $setting->api_key,
            ]);

        $rawBody = trim((string) $response->body());

        if (! $response->successful()) {
            return [
                'success' => false,
                'message' => 'Balance check failed. '.$rawBody,
            ];
        }

        $decoded = json_decode($rawBody, true);
        $balance = $this->extractBalance($decoded, $rawBody);

        if ($balance === null) {
            return [
                'success' => false,
                'message' => 'Unexpected balance response: '.$rawBody,
            ];
        }

        $setting->update([
            'current_balance' => $balance,
            'last_balance_checked_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'SMS balance refreshed successfully.',
            'balance' => $balance,
            'raw_response' => $rawBody,
        ];
    }

    public function sendSms(string $phone, string $message): array
    {
        $normalizedPhone = $this->normalizePhone($phone);

        if (! $normalizedPhone) {
            return [
                'success' => false,
                'code' => '206',
                'status_text' => 'Invalid Mobile Number',
                'raw_response' => 'Invalid number format.',
                'phone' => null,
                'transaction_id' => null,
            ];
        }

        return $this->sendRequest(self::SEND_URL, [
            'MobileNumber' => $normalizedPhone,
            'Message' => $message,
        ], $normalizedPhone);
    }

    public function sendBulkSms(string $phones, string $message): array
    {
        return $this->sendRequest(self::BULK_SEND_URL, [
            'MobileNumber' => $phones,
            'Message' => $message,
        ], $phones);
    }

    public function statusText(?string $code): string
    {
        return match ($code) {
            '200' => 'SMS submitted successfully',
            '205' => 'Invalid Message Content',
            '206' => 'Invalid Mobile Number',
            '207' => 'Invalid Transaction Type',
            '208' => 'Invalid Sender ID',
            '209' => 'SMS Length cross the Max level',
            '210' => 'Invalid CampaignId',
            '213' => 'Parameter Mismatch',
            '216' => 'Insufficient Balance',
            '221' => 'SMS Sending Failed',
            '401' => 'Unauthorized request',
            '500' => 'MiMSMS internal error',
            default => 'Unknown gateway response',
        };
    }

    private function sendRequest(string $url, array $payload, string $phone): array
    {
        $setting = $this->getSetting();

        if (blank($setting->username) || blank($setting->api_key) || blank($setting->sender_id)) {
            return [
                'success' => false,
                'code' => null,
                'status_text' => 'SMS Username, API Key, and Sender Name are required.',
                'raw_response' => null,
                'phone' => $phone,
                'transaction_id' => null,
            ];
        }

        $body = [
            'UserName' => $setting->username,
            'Apikey' => $setting->api_key,
            'CampaignId' => null,
            'SenderName' => $setting->sender_id,
            'TransactionType' => $setting->transaction_type ?: 'T',
        ] + $payload;

        $response = Http::asJson()
            ->timeout(60)
            ->withoutVerifying()
            ->post($url, $body);

        $rawBody = trim((string) $response->body());
        $decoded = json_decode($rawBody, true);
        $code = $this->extractStatusCode($decoded, $response->status());
        $statusText = $this->extractStatusText($decoded, $rawBody, $code);

        return [
            'success' => $response->successful() && $this->isSuccessfulStatus($decoded, $code),
            'code' => $code,
            'status_text' => $statusText,
            'raw_response' => $rawBody,
            'phone' => $phone,
            'transaction_id' => is_array($decoded) ? ($decoded['trxnId'] ?? null) : null,
        ];
    }

    private function extractBalance(mixed $decoded, string $rawBody): ?float
    {
        if (is_numeric($rawBody)) {
            return (float) $rawBody;
        }

        if (! is_array($decoded)) {
            return null;
        }

        foreach (['balance', 'currentBalance', 'availableBalance'] as $key) {
            if (isset($decoded[$key]) && is_numeric($decoded[$key])) {
                return (float) $decoded[$key];
            }
        }

        if (isset($decoded['responseResult']) && is_numeric($decoded['responseResult'])) {
            return (float) $decoded['responseResult'];
        }

        return null;
    }

    private function extractStatusCode(mixed $decoded, int $httpStatus): ?string
    {
        if (is_array($decoded) && isset($decoded['statusCode'])) {
            return (string) $decoded['statusCode'];
        }

        return (string) $httpStatus;
    }

    private function extractStatusText(mixed $decoded, string $rawBody, ?string $code): string
    {
        if (is_array($decoded)) {
            $parts = array_filter([
                isset($decoded['status']) ? trim((string) $decoded['status']) : null,
                isset($decoded['responseResult']) ? trim((string) $decoded['responseResult']) : null,
                isset($decoded['trxnId']) ? 'Transaction ID: '.trim((string) $decoded['trxnId']) : null,
            ]);

            if ($parts !== []) {
                return implode(' | ', $parts);
            }
        }

        return $rawBody !== '' ? $rawBody : $this->statusText($code);
    }

    private function isSuccessfulStatus(mixed $decoded, ?string $code): bool
    {
        if (! is_array($decoded)) {
            return false;
        }

        $status = strtolower(trim((string) ($decoded['status'] ?? '')));

        if (in_array($status, ['success', 'successful', 'ok'], true)) {
            return true;
        }

        return $code === '200';
    }
}
