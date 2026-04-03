<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['order_number', 'user_id', 'affiliate_id', 'order_type', 'total_amount', 'delivery_charge', 'discount_amount', 'order_status', 'track_id', 'courier_api_response'];

    protected function casts(): array
    {
        return [
            'courier_api_response' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function affiliateCommissions()
    {
        return $this->hasMany(AffiliateCommission::class);
    }

    public function stockOutLogs()
    {
        return $this->hasMany(StockOutLog::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(AffiliateWalletTransaction::class);
    }
}
