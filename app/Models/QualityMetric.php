<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualityMetric extends Model {
    protected $table = 'quality_metrics'; // matches your table
    protected $fillable = ['name', 'unit']; // optional but good
}
