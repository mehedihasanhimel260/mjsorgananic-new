<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Affiliate extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'affiliates';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'affiliate_code',
        'balance',
        'status',
        'last_login_at',
    ];

    /**
     * Hidden attributes
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Attribute casting
     */
    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'balance' => 'decimal:2',
            'password' => 'hashed',
        ];
    }
}
