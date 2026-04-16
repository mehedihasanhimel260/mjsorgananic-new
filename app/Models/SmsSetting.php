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
        'current_balance',
        'last_balance_checked_at',
        'last_bulk_message',
        'last_single_message',
    ];

    protected function casts(): array
    {
        return [
            'current_balance' => 'decimal:2',
            'last_balance_checked_at' => 'datetime',
        ];
    }
}
