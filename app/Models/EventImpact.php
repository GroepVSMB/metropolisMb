<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventImpact extends Model
{
    // Updated fillable fields
    protected $fillable = ['simulation_event_id', 'quality_metric_id', 'impact'];

    // Updated relationship
    public function qualityMetric()
    {
        return $this->belongsTo(QualityMetric::class);
    }
}
