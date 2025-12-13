<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CityFunction extends Model
{

    protected $fillable = ['name', 'category_id', 'livability_number', 'image'];

    // Relationship: A CityFunction belongs to a Category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}