<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_id',
        'product_id',
        'tracking_code',
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
