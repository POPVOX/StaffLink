<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Correction extends Model
{
    protected $fillable = [
        'question_pattern',
        'keywords',
        'answer_text',
        'priority',
        'active',
        'example_embedding',
    ];

    protected $casts = [
        'active'            => 'boolean',
        'example_embedding' => 'array',
    ];
}
