<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = ['user_id', 'grid_index', 'content', 'is_resolved'];

    protected $with = ['user']; // Altijd de auteur laden

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}