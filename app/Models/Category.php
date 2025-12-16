<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'color_hex', 'text_color'];

    // Relationship: One Category has many CityFunctions
    public function cityFunctions(): HasMany
    {
        return $this->hasMany(CityFunction::class);
    }

    public function incompatibleCategories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_incompatibilities', 'category_id', 'incompatible_category_id');
    }
}
