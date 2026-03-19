<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SteadfastSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_key',
        'secret_key',
        'current_balance',
        'last_balance_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'secret_key' => 'encrypted',
            'current_balance' => 'decimal:2',
            'last_balance_synced_at' => 'datetime',
        ];
    }
}
