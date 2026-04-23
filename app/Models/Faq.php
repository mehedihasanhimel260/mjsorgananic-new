<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'status',
        'keyword',
    ];

    protected function casts(): array
    {
        return [
            'keyword' => 'array',
        ];
    }

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PENDING = 'pending';
    public const STATUS_INACTIVE = 'inactive';

    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_PENDING,
            self::STATUS_INACTIVE,
        ];
    }

    public function getKeywordListAttribute(): array
    {
        if (is_array($this->keyword)) {
            return array_values(array_filter($this->keyword));
        }

        if (is_string($this->keyword) && $this->keyword !== '') {
            return array_values(array_filter(array_map('trim', explode(',', $this->keyword))));
        }

        return [];
    }
}
