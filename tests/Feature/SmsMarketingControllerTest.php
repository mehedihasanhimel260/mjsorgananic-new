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
        ->assertSee('Automation Schedule', false)
        ->assertSee('Start Date', false);
});

it('validates and saves mim sms credentials and schedule settings', function () {
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
        'schedule_enabled' => '1',
        'schedule_day_of_week' => '5',
        'schedule_time' => '10:00',
        'schedule_start_date' => '2026-04-25',
    ]);

    $response->assertRedirect(route('admin.sms-settings.index'));

    $this->assertDatabaseHas('sms_settings', [
        'username' => 'mim-user',
        'sender_id' => 'DemoSender',
        'api_key' => 'secret',
        'transaction_type' => 'T',
        'schedule_enabled' => true,
        'schedule_day_of_week' => 5,
        'schedule_time' => '10:00',
        'schedule_start_date' => '2026-04-25',
    ]);
});

it('requires the mim sms fields and schedule fields when updating settings', function () {
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
            'schedule_day_of_week' => '9',
            'schedule_time' => '25:00',
            'schedule_start_date' => '',
        ]);

    $response->assertRedirect(route('admin.sms-settings.index'))
        ->assertSessionHasErrors(['username', 'sender_id', 'api_key', 'transaction_type', 'schedule_day_of_week', 'schedule_time', 'schedule_start_date']);

    expect(SmsSetting::query()->count())->toBe(0);
});
