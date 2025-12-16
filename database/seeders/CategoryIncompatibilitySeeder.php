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
        $natuur = Category::where('name', 'Natuur')->first();

        // Safety check: ensure categories exist before linking
        if ($wonen && $industrie) {
            // Rule: Housing cannot be next to Industry
            // We use syncWithoutDetaching to avoid duplicate errors
            $wonen->incompatibleCategories()->syncWithoutDetaching([$industrie->id]);
            $industrie->incompatibleCategories()->syncWithoutDetaching([$wonen->id]);
        }

        if ($industrie && $natuur) {
            // Rule: Industry cannot be next to Nature
            $industrie->incompatibleCategories()->syncWithoutDetaching([$natuur->id]);
            $natuur->incompatibleCategories()->syncWithoutDetaching([$industrie->id]);
        }
    }
}
