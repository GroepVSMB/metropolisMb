<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // NEW
use Illuminate\Database\Eloquent\Model;

class Simulation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'grid_state', 'vector_state', 'grid_width', 'grid_height', 'grid_type', 'schedule_state', 'current_tick', 'status', 'speed'];

    protected $casts = [
        'grid_state' => 'array',
        'vector_state' => 'array',
        'schedule_state' => 'array',
        'current_tick' => 'integer',
        'speed' => 'integer',
        'grid_width' => 'integer',
        'grid_height' => 'integer'
    ];

    public function events()
    {
        return $this->hasMany(SimulationEvent::class);
    }
}
