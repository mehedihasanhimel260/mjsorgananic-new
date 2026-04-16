<?php

use App\Models\Admin;
use App\Models\SmsSetting;

it('shows mim sms fields on the settings page', function () {
    $admin = Admin::query()->create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'phone' => '01711111111',
        'password' => 'password',
    ]);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.sms-settings.index'));

    $response->assertOk()
        ->assertSee('Username', false)
        ->assertSee('Sender Name', false)
        ->assertSee('Transaction Type', false)
        ->assertSee('Transactional (T)', false);
});

it('validates and saves mim sms credentials', function () {
    $admin = Admin::query()->create([
        'name' => 'Admin User',
        'email' => 'admin2@example.com',
        'phone' => '01711111112',
        'password' => 'password',
    ]);

    $response = $this->actingAs($admin, 'admin')->post(route('admin.sms-settings.update'), [
        'username' => 'mim-user',
        'sender_id' => 'DemoSender',
        'api_key' => 'secret',
        'transaction_type' => 'T',
    ]);

    $response->assertRedirect(route('admin.sms-settings.index'));

    $this->assertDatabaseHas('sms_settings', [
        'username' => 'mim-user',
        'sender_id' => 'DemoSender',
        'api_key' => 'secret',
        'transaction_type' => 'T',
    ]);
});

it('requires the new mim sms fields when updating settings', function () {
    $admin = Admin::query()->create([
        'name' => 'Admin User',
        'email' => 'admin3@example.com',
        'phone' => '01711111113',
        'password' => 'password',
    ]);

    $response = $this->from(route('admin.sms-settings.index'))
        ->actingAs($admin, 'admin')
        ->post(route('admin.sms-settings.update'), [
            'sender_id' => '',
            'api_key' => '',
            'transaction_type' => 'X',
        ]);

    $response->assertRedirect(route('admin.sms-settings.index'))
        ->assertSessionHasErrors(['username', 'sender_id', 'api_key', 'transaction_type']);

    expect(SmsSetting::query()->count())->toBe(0);
});
