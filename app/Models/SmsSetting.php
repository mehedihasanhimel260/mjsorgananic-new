<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'sender_id',
        'api_key',
        'transaction_type',
        'schedule_enabled',
        'schedule_day_of_week',
        'schedule_time',
        'schedule_start_date',
        'current_balance',
        'last_balance_checked_at',
        'last_bulk_message',
        'last_single_message',
    ];

    protected function casts(): array
    {
        return [
            'schedule_enabled' => 'boolean',
            'schedule_start_date' => 'date',
            'current_balance' => 'decimal:2',
            'last_balance_checked_at' => 'datetime',
        ];
    }
}
