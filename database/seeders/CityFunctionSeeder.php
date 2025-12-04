<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class CityFunctionSeeder extends Seeder
{
    public function run(): void
    {
        // Clears old data to prevent duplicates when re-seeding
        DB::table('city_functions')->truncate();

        DB::table('city_functions')->insert([
            [
                'name' => 'Sociale Huur',
                'category' => 'Wonen',
                'color_hex' => '#89CFF0', // Sky Blue
                'text_color' => '#333333',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Luxe Flat',
                'category' => 'Wonen',
                'color_hex' => '#1f4e79', // Metro Blue
                'text_color' => '#ffffff',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Stadspark',
                'category' => 'Groen',
                'color_hex' => '#448a28', // Metro Green
                'text_color' => '#ffffff',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Industrie',
                'category' => 'Werken',
                'color_hex' => '#666666', // Metro Grey
                'text_color' => '#ffffff',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Winkel',
                'category' => 'Dienst',
                'color_hex' => '#d6aeb4', // Metro Pink
                'text_color' => '#333333',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Politiebureau',
                'category' => 'Veiligheid',
                'color_hex' => '#be1e2d', // Metro Red
                'text_color' => '#ffffff',
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);
    }
}
