<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Simulation extends Model
{
    protected $fillable = ['name', 'grid_state'];

    protected $casts = [
        'grid_state' => 'array', // Auto-convert JSON to array
    ];
}
