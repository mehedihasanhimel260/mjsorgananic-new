<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsCampaignRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'sms_campaign_id',
        'user_id',
        'phone',
        'week_key',
        'batch_number',
        'status',
        'attempts',
        'gateway_transaction_id',
        'status_code',
        'status_text',
        'gateway_response',
        'last_attempt_at',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'last_attempt_at' => 'datetime',
            'sent_at' => 'datetime',
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
}
