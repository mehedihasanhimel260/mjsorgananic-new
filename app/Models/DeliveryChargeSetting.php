<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryChargeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'inside_dhaka_delivery_charge',
        'outside_dhaka_delivery_charge',
        'custom_delivery_charge',
        'free_delivery_min_order_amount',
    ];

    protected function casts(): array
    {
        return [
            'inside_dhaka_delivery_charge' => 'decimal:2',
            'outside_dhaka_delivery_charge' => 'decimal:2',
            'custom_delivery_charge' => 'decimal:2',
            'free_delivery_min_order_amount' => 'decimal:2',
        ];
    }
}
