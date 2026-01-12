<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventImpact extends Model
{
    protected $fillable = ['simulation_event_id', 'category_id', 'livability_adjustment'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}