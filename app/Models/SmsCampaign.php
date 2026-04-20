<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'sms_template_id',
        'title',
        'message',
        'campaign_type',
        'week_key',
        'week_starts_at',
        'week_ends_at',
        'status',
        'batch_size',
        'total_recipients',
        'pending_recipients',
        'processing_recipients',
        'sent_recipients',
        'failed_recipients',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'week_starts_at' => 'datetime',
            'week_ends_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function template()
    {
        return $this->belongsTo(SmsTemplate::class, 'sms_template_id');
    }

    public function recipients()
    {
        return $this->hasMany(SmsCampaignRecipient::class);
    }
}
