<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ticket_number',
        'status',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conversions()
    {
        return $this->hasMany(Conversion::class)->orderBy('created_at');
    }

    public function latestConversion()
    {
        return $this->hasOne(Conversion::class)->latestOfMany();
    }
}
