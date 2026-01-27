<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Import this
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CityFunction extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'category_id', 'livability_number', 'image'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // New Relationship: Users who have acknowledged this function
    public function acknowledgedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'city_function_user')->withTimestamps();
    }

    public function impacts()
    {
        return $this->hasMany(FunctionImpact::class);
    }

    // Helper to get a specific impact score easily
    public function getImpactOn($metricId)
    {
        $impact = $this->impacts->where('quality_metric_id', $metricId)->first();
        return $impact ? $impact->impact : 0;
    }
}
