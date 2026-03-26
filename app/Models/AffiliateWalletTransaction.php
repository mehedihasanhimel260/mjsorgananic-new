<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateWalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_id',
        'type',
        'amount',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }
}
