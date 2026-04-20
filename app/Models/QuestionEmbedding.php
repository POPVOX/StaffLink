<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class QuestionEmbedding extends Model
{
    protected $table = 'question_embeddings';

    protected $fillable = ['message_id', 'embedding'];

    protected function casts(): array
    {
        return [
            'embedding' => 'array',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
