<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'short_description',
        'long_description',
        'keywords',
        'image',
        'sku',
        'selling_price',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function commissions()
    {
        return $this->hasMany(ProductCommission::class);
    }

    public function affiliateCommissions()
    {
        return $this->hasMany(AffiliateCommission::class);
    }

}
