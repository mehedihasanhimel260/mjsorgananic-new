<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'is_weekly_active',
    ];

    protected function casts(): array
    {
        return [
            'is_weekly_active' => 'boolean',
        ];
    }

    public function campaigns()
    {
        return $this->hasMany(SmsCampaign::class);
    }
}
