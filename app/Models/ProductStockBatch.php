<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStockBatch extends Model
{
    use HasFactory;

    protected $table = 'product_stock_batches';

    protected $fillable = [
        'product_id',
        'quantity',
        'total_cost',
        'cost_per_unit',
        'note',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}