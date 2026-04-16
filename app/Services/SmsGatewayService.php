<?php

namespace App\Services;

use App\Models\SmsSetting;
use Illuminate\Support\Facades\Http;

class SmsGatewayService
{
    private const SEND_URL = 'https://api.mimsms.com/api/SmsSending/SMS';
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
        $setting = $this->getSetting();

        if (blank($setting->username) || blank($setting->api_key) || blank($setting->sender_id)) {
            return [
                'success' => false,
                'code' => null,
                'status_text' => 'SMS Username, API Key, and Sender Name are required.',
                'raw_response' => null,
            ];
        }

        $normalizedPhone = $this->normalizePhone($phone);

        if (! $normalizedPhone) {
            return [
                'success' => false,
                'code' => '1001',
                'status_text' => $this->statusText('1001'),
                'raw_response' => 'Invalid number format.',
            ];
        }

        $response = Http::asJson()
            ->timeout(30)
            ->withoutVerifying()
            ->post(self::SEND_URL, [
                'UserName' => $setting->username,
                'Apikey' => $setting->api_key,
                'MobileNumber' => $normalizedPhone,
                'SenderName' => $setting->sender_id,
                'TransactionType' => $setting->transaction_type ?: 'T',
                'Message' => $message,
            ]);

        $rawBody = trim((string) $response->body());
        $decoded = json_decode($rawBody, true);
        $code = $this->extractStatusCode($decoded, $response->status());
        $statusText = $this->extractStatusText($decoded, $rawBody, $code);

        return [
            'success' => $response->successful() && $this->isSuccessfulStatus($decoded, $code),
            'code' => $code,
            'status_text' => $statusText,
            'raw_response' => $rawBody,
            'phone' => $normalizedPhone,
        ];
    }

    public function statusText(?string $code): string
    {
        return match ($code) {
            '1001' => 'Invalid Number',
            '200' => 'SMS submitted successfully',
            '201' => 'SMS submitted successfully',
            '202' => 'SMS submitted successfully',
            '400' => 'Bad request',
            '401' => 'Authentication failed',
            '403' => 'Gateway rejected the request',
            '404' => 'MiMSMS endpoint not found',
            '422' => 'Request validation failed',
            '500' => 'MiMSMS internal error',
            default => 'Unknown gateway response',
        };
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

        return in_array($code, ['200', '201', '202'], true);
    }
}
