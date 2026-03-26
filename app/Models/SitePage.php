<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SitePage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'short_intro',
        'content',
        'banner_image',
        'meta_title',
        'meta_description',
        'status',
        'show_in_menu',
    ];

    protected function casts(): array
    {
        return [
            'show_in_menu' => 'boolean',
        ];
    }
}
