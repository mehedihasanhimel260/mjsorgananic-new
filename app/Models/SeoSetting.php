<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_name',
        'title',
        'subtitle',
        'meta_description',
        'meta_keywords',
        'og_url',
        'og_site_name',
        'og_title',
        'og_description',
        'og_image',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'apple_touch_icon',
        'favicon_32',
        'favicon_16',
        'mask_icon',
        'mask_icon_color',
    ];
}
