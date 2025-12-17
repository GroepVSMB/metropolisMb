<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Import this

class CityFunction extends Model
{
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
}