<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoryIncompatibilitySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get the Categories (Make sure these names match your DB exactly!)
        $wonen = Category::where('name', 'Wonen')->first();
        $industrie = Category::where('name', 'Industrie')->first();
        $groen = Category::where('name', 'Groen')->first();

        // Safety check: ensure categories exist before linking
        if ($wonen && $industrie) {
            // Rule: Housing cannot be next to Industry
            // We use syncWithoutDetaching to avoid duplicate errors
            $wonen->incompatibleCategories()->syncWithoutDetaching([$industrie->id]);
        }

        if ($industrie && $groen) {
            // Rule: Industry cannot be next to Nature
            $industrie->incompatibleCategories()->syncWithoutDetaching([$groen->id]);

        }
    }
}
