<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionEmbedding extends Model
{
    protected $table = 'question_embeddings';
    protected $fillable = ['message_id', 'embedding'];
    protected $casts = [
        'embedding' => 'array',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
