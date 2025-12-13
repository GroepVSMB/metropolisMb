<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks to allow truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('categories')->insert([
            [
                'name' => 'Wonen',
                'color_hex' => '#1f4e79', // Metro Blue (Unified color for Wonen)
                'text_color' => '#ffffff',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Groen',
                'color_hex' => '#448a28', // Metro Green
                'text_color' => '#ffffff',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Industrie',
                'color_hex' => '#666666', // Metro Grey
                'text_color' => '#ffffff',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Dienst',
                'color_hex' => '#d6aeb4', // Metro Pink
                'text_color' => '#333333',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Veiligheid',
                'color_hex' => '#be1e2d', // Metro Red
                'text_color' => '#ffffff',
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);
    }
}