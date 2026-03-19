<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOutLog extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'batch_id', 'order_id', 'quantity', 'cost_per_unit'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(ProductStockBatch::class, 'batch_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}