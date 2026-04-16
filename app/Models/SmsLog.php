<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sent_by_admin_id',
        'phone',
        'message',
        'send_type',
        'status_code',
        'status_text',
        'gateway_response',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'sent_by_admin_id');
    }
}
