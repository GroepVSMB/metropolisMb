<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CityFunction extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'category_id', 'livability_number', 'image'];

    // Relationship: A CityFunction belongs to a Category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function acknowledgedByUsers()
    {
        return $this->belongsToMany(
            User::class,
            'city_function_acknowledgements',
            'city_function_id',
            'user_id'
        )->withPivot('acknowledged_at');
    }
}