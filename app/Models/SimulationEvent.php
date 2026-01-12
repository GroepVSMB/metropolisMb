<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimulationEvent extends Model
{
    protected $fillable = ['name', 'type', 'duration_minutes', 'recurrence_interval_minutes'];

    public function impacts()
    {
        return $this->hasMany(EventImpact::class);
    }
}