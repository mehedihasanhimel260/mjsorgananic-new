<?php

use App\Models\SmsSetting;
use App\Services\SmsGatewayService;
use Illuminate\Support\Facades\Http;

it('refreshes balance from mim sms json response', function () {
    SmsSetting::query()->create([
        'username' => 'demo-user',
        'sender_id' => 'DemoSender',
        'api_key' => 'secret',
        'transaction_type' => 'T',
    ]);

    Http::fake([
        'https://api.mimsms.com/api/SmsSending/balanceCheck' => Http::response([
            'statusCode' => 200,
            'status' => 'success',
            'responseResult' => '125.75',
        ], 200),
    ]);

    $result = app(SmsGatewayService::class)->refreshBalance();

    expect($result['success'])->toBeTrue()
        ->and($result['balance'])->toBe(125.75);

    $setting = SmsSetting::query()->first();

    expect((float) $setting->current_balance)->toBe(125.75)
        ->and($setting->last_balance_checked_at)->not->toBeNull();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.mimsms.com/api/SmsSending/balanceCheck'
            && $request['UserName'] === 'demo-user'
            && $request['Apikey'] === 'secret';
    });
});

it('sends sms to mim sms with normalized payload and parses success response', function () {
    SmsSetting::query()->create([
        'username' => 'demo-user',
        'sender_id' => 'DemoSender',
        'api_key' => 'secret',
        'transaction_type' => 'T',
    ]);

    Http::fake([
        'https://api.mimsms.com/api/SmsSending/SMS' => Http::response([
            'statusCode' => 200,
            'status' => 'success',
            'responseResult' => 'SMS Accepted',
            'trxnId' => 'TXN-123',
        ], 200),
    ]);

    $result = app(SmsGatewayService::class)->sendSms('01700000000', 'Hello');

    expect($result['success'])->toBeTrue()
        ->and($result['code'])->toBe('200')
        ->and($result['phone'])->toBe('8801700000000')
        ->and($result['status_text'])->toContain('success')
        ->and($result['status_text'])->toContain('SMS Accepted')
        ->and($result['status_text'])->toContain('TXN-123');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.mimsms.com/api/SmsSending/SMS'
            && $request['UserName'] === 'demo-user'
            && $request['Apikey'] === 'secret'
            && $request['MobileNumber'] === '8801700000000'
            && $request['SenderName'] === 'DemoSender'
            && $request['TransactionType'] === 'T'
            && $request['Message'] === 'Hello';
    });
});

it('fails gracefully when balance response is malformed', function () {
    SmsSetting::query()->create([
        'username' => 'demo-user',
        'sender_id' => 'DemoSender',
        'api_key' => 'secret',
        'transaction_type' => 'T',
    ]);

    Http::fake([
        'https://api.mimsms.com/api/SmsSending/balanceCheck' => Http::response('not-json', 200),
    ]);

    $result = app(SmsGatewayService::class)->refreshBalance();

    expect($result['success'])->toBeFalse()
        ->and($result['message'])->toContain('Unexpected balance response');
});
