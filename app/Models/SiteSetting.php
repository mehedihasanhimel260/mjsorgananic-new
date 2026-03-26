<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_name',
        'site_tagline',
        'logo',
        'favicon',
        'contact_phone',
        'whatsapp_number',
        'support_email',
        'default_address',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'site_active',
        'chat_active',
        'affiliate_active',
        'footer_logo',
        'footer_text',
        'footer_quick_links_title',
        'copyright_text',
    ];

    protected function casts(): array
    {
        return [
            'site_active' => 'boolean',
            'chat_active' => 'boolean',
            'affiliate_active' => 'boolean',
        ];
    }
}
