<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sms_campaign_id',
        'sent_by_admin_id',
        'phone',
        'message',
        'send_type',
        'campaign_key',
        'gateway_transaction_id',
        'status_code',
        'status_text',
        'delivery_status',
        'delivery_attempts',
        'delivery_status_checked_at',
        'delivery_finalized_at',
        'gateway_response',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'delivery_status_checked_at' => 'datetime',
            'delivery_finalized_at' => 'datetime',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(SmsCampaign::class, 'sms_campaign_id');
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
