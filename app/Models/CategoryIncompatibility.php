<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;

class CategoryIncompatibility extends Model
{
    protected $fillable = [
        'category_id',
        'incompatible_category_id'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function incompatibleCategory()
    {
        return $this->belongsTo(Category::class, 'incompatible_category_id');
    }
}
