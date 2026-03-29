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
        'keyword',
    ];

    protected function casts(): array
    {
        return [
            'keyword' => 'array',
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
