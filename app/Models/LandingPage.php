<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'status',
        'hero_title',
        'hero_subtitle',
        'hero_description',
        'hero_image',
        'ingredients_title',
        'ingredients',
        'benefits_title',
        'benefits',
        'how_to_use_title',
        'how_to_use',
        'reviews_title',
        'customer_reviews',
        'gallery_images',
        'checkout_title',
        'final_cta_title',
        'final_cta_text',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'ingredients' => 'array',
            'benefits' => 'array',
            'how_to_use' => 'array',
            'customer_reviews' => 'array',
            'gallery_images' => 'array',
        ];
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('landing_page_product.sort_order')
            ->orderBy('products.id');
    }

    public function primaryProduct(): ?Product
    {
        return $this->products->first();
    }
}
