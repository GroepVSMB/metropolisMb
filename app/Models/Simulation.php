<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // NEW
use Illuminate\Database\Eloquent\Model;

class Simulation extends Model
{
    use HasFactory; // NEW

    protected $fillable = ['name', 'grid_state', 'grid_width', 'grid_height', 'schedule_state', 'current_tick', 'status', 'speed'];

    protected $casts = [
        'grid_state' => 'array', // Auto-convert JSON to array
        'schedule_state' => 'array',
    ];

    public function events()
    {
        return $this->hasMany(SimulationEvent::class);
    }
}
