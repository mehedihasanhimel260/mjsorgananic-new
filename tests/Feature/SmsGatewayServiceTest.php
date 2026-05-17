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

it('sends one-to-many sms and exposes the transaction id', function () {
    SmsSetting::query()->create([
        'username' => 'demo-user',
        'sender_id' => 'DemoSender',
        'api_key' => 'secret',
        'transaction_type' => 'T',
    ]);

    Http::fake([
        'https://api.mimsms.com/api/SmsSending/OneToMany' => Http::response([
            'statusCode' => 200,
            'status' => 'Success',
            'responseResult' => 'SMS Send Successfuly',
            'trxnId' => 'BATCH-TXN-1',
        ], 200),
    ]);

    $result = app(SmsGatewayService::class)->sendBulkSms('8801700000000,8801700000001', 'Weekly hello');

    expect($result['success'])->toBeTrue()
        ->and($result['transaction_id'])->toBe('BATCH-TXN-1');
});

it('maps delivered dlr response to final delivered status', function () {
    SmsSetting::query()->create([
        'username' => 'demo-user',
        'sender_id' => 'DemoSender',
        'api_key' => 'secret',
        'transaction_type' => 'T',
    ]);

    Http::fake([
        'https://api.mimsms.com/api/SmsSending/DlrApi' => Http::response([
            'statusCode' => '200',
            'status' => 'Ok',
            'trxnId' => 'BATCH-TXN-1',
            'dlrCode' => '0',
            'operatorStatus' => 'DELIVERED',
            'receiverMobile' => '8801700000000',
        ], 200),
    ]);

    $result = app(SmsGatewayService::class)->checkDeliveryStatus('BATCH-TXN-1', '8801700000000');

    expect($result['success'])->toBeTrue()
        ->and($result['final'])->toBeTrue()
        ->and($result['delivery_status'])->toBe('delivered')
        ->and($result['status_text'])->toContain('Operator Status: DELIVERED');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.mimsms.com/api/SmsSending/DlrApi'
            && $request['ApiKey'] === 'secret'
            && $request['UserName'] === 'demo-user'
            && $request['MobileNumber'] === '8801700000000'
            && $request['trxnId'] === 'BATCH-TXN-1';
    });
});

it('maps terminal dlr operator status to failed', function () {
    SmsSetting::query()->create([
        'username' => 'demo-user',
        'sender_id' => 'DemoSender',
        'api_key' => 'secret',
        'transaction_type' => 'T',
    ]);

    Http::fake([
        'https://api.mimsms.com/api/SmsSending/DlrApi' => Http::response([
            'statusCode' => '200',
            'status' => 'Ok',
            'trxnId' => 'BATCH-TXN-2',
            'dlrCode' => '1',
            'operatorStatus' => 'UNDELIVERED',
            'receiverMobile' => '8801700000001',
        ], 200),
    ]);

    $result = app(SmsGatewayService::class)->checkDeliveryStatus('BATCH-TXN-2', '8801700000001');

    expect($result['final'])->toBeTrue()
        ->and($result['delivery_status'])->toBe('failed');
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
