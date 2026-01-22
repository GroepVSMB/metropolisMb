<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FunctionImpact extends Model
{
    // Make sure these fields are fillable so your Controller can save them
    protected $fillable = ['city_function_id', 'quality_metric_id', 'impact'];

    // 1. Link back to the Function (e.g. "Stadspark")
    public function cityFunction()
    {
        return $this->belongsTo(CityFunction::class);
    }

    // 2. THIS IS THE MISSING METHOD CAUSING YOUR ERROR
    public function qualityMetric()
    {
        return $this->belongsTo(QualityMetric::class);
    }
}
