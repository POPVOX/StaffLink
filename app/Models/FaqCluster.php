<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FaqCluster extends Model
{
    protected $table = 'faq_clusters';

    protected $fillable = ['representative_text', 'frequency'];

    public function messages(): BelongsToMany
    {
        return $this->belongsToMany(
            Message::class,
            'faq_cluster_message',
            'cluster_id',
            'message_id'
        );
    }
}
