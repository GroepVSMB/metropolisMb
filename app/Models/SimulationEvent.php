<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimulationEvent extends Model
{
    // Removed 'category_id' from fillable
    protected $fillable = ['name', 'type', 'duration_minutes', 'recurrence_interval_minutes'];

    public function impacts()
    {
        return $this->hasMany(EventImpact::class);
    }

    // NEW: Many-to-Many Relationship
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
