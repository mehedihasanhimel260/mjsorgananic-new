<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FbSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'fb_page_id',
        'access_token',
        'pixel_id',
        'event_id',
        'verify_token',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
        ];
    }
}
