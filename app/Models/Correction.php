<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Correction extends Model
{
    protected $fillable = [
        'question_pattern',
        'answer_text',
        'priority',
        'active',
        'example_embedding',
    ];

    protected $casts = [
        'active'            => 'boolean',
        'example_embedding' => 'array',
    ];

    public function keywords(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Keyword::class);
    }
}
