<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\CityFunction;
use App\Models\QualityMetric;
use App\Models\FunctionImpact;

class CityFunctionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Clean up old data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        FunctionImpact::truncate();
        CityFunction::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Fetch Categories (Ensure they exist)
        $wonen = Category::where('name', 'Wonen')->first()->id ?? 1;
        $groen = Category::where('name', 'Groen')->first()->id ?? 2;
        $werken = Category::where('name', 'Industrie')->first()->id ?? 3;
        $dienst = Category::where('name', 'Dienst')->first()->id ?? 4;
        $veiligheid = Category::where('name', 'Veiligheid')->first()->id ?? 5;

        // 3. Fetch Metrics (Using firstOrCreate to PREVENT CRASHES)
        // This ensures that if the metric is missing, it gets created right here.
        $air = QualityMetric::firstOrCreate(['name' => 'Luchtkwaliteit']);
        $noise = QualityMetric::firstOrCreate(['name' => 'Geluidshinder']);
        $traffic = QualityMetric::firstOrCreate(['name' => 'Verkeersdoorstroming']);
        $housing = QualityMetric::firstOrCreate(['name' => 'Huiswaarde']);
        $safety = QualityMetric::firstOrCreate(['name' => 'Veiligheid']);
        $energy = QualityMetric::firstOrCreate(['name' => 'Energieverbruik']);

        // 4. Define Functions & Their Specific Impacts
        $functions = [
            [
                'name' => 'Sociale Huur',
                'category_id' => $wonen,
                'image' => 'uploads/sociale_huur.jpg',
                'impacts' => [
                    ['id' => $housing->id, 'val' => 20, 'cond' => 'always'],
                    ['id' => $safety->id, 'val' => 5, 'cond' => 'always'],
                    ['id' => $traffic->id, 'val' => -5, 'cond' => 'always'],
                    ['id' => $energy->id, 'val' => -10, 'cond' => 'night_only'], // High energy use at night
                ]
            ],
            [
                'name' => 'Luxe Flat',
                'category_id' => $wonen,
                'image' => 'uploads/luxe_flat.jpg',
                'impacts' => [
                    ['id' => $housing->id, 'val' => 40, 'cond' => 'always'],
                    ['id' => $energy->id, 'val' => 10, 'cond' => 'always'], // Energy efficient
                    ['id' => $traffic->id, 'val' => -10, 'cond' => 'always'],
                ]
            ],
            [
                'name' => 'Stadspark',
                'category_id' => $groen,
                'image' => 'uploads/stadspark.jpg',
                'impacts' => [
                    ['id' => $air->id, 'val' => 30, 'cond' => 'always'],
                    ['id' => $noise->id, 'val' => 20, 'cond' => 'always'],
                    ['id' => $housing->id, 'val' => 15, 'cond' => 'always'],
                    ['id' => $safety->id, 'val' => 10, 'cond' => 'day_only'],
                    ['id' => $safety->id, 'val' => -5, 'cond' => 'night_only'], // Unsafe at night
                ]
            ],
            [
                'name' => 'Staal Fabriek',
                'category_id' => $werken,
                'image' => 'uploads/staal_fabriek.jpg',
                'impacts' => [
                    ['id' => $air->id, 'val' => -50, 'cond' => 'always'],
                    ['id' => $noise->id, 'val' => -40, 'cond' => 'always'],
                    ['id' => $traffic->id, 'val' => -20, 'cond' => 'always'],
                    ['id' => $housing->id, 'val' => -30, 'cond' => 'always'],
                ]
            ],
            [
                'name' => 'Winkel',
                'category_id' => $dienst,
                'image' => 'uploads/winkel.jpg',
                'impacts' => [
                    ['id' => $housing->id, 'val' => 10, 'cond' => 'always'],
                    ['id' => $traffic->id, 'val' => -15, 'cond' => 'day_only'], 
                    ['id' => $safety->id, 'val' => 5, 'cond' => 'day_only'],
                    ['id' => $energy->id, 'val' => -10, 'cond' => 'day_only'], // Lights/AC during day
                ]
            ],
            [
                'name' => 'Politiebureau',
                'category_id' => $veiligheid,
                'image' => 'uploads/politiebureau.jpg',
                'impacts' => [
                    ['id' => $safety->id, 'val' => 50, 'cond' => 'always'],
                    ['id' => $noise->id, 'val' => -5, 'cond' => 'always'],
                    ['id' => $housing->id, 'val' => 5, 'cond' => 'always'],
                ]
            ],
            // NEW ITEMS
            [
                'name' => 'Zonnepaneel',
                'category_id' => $groen, 
                'image' => 'https://solarmagazine.nl/storage/images/2023/12/zonnepanelen-dak-huis-2.jpg',
                'impacts' => [
                    ['id' => $energy->id, 'val' => 30, 'cond' => 'day_only'], 
                    ['id' => $housing->id, 'val' => 5, 'cond' => 'always'],
                ]
            ],
            [
                'name' => 'Bar / Cafe',
                'category_id' => $dienst,
                'image' => 'https://entree-assets.s3.eu-central-1.amazonaws.com/s3fs-public/styles/header_image/public/2023-08/Bar%20The%20Tailor%20Amsterdam%20Krasnapolsky.jpg?h=a1532f6a&itok=D3g8tLpS',
                'impacts' => [
                    ['id' => $noise->id, 'val' => -5, 'cond' => 'day_only'],
                    ['id' => $noise->id, 'val' => -30, 'cond' => 'night_only'], 
                    ['id' => $safety->id, 'val' => -5, 'cond' => 'night_only'], 
                    ['id' => $housing->id, 'val' => -5, 'cond' => 'always'],
                ]
            ]
        ];

        // 5. Insert Data
        foreach ($functions as $data) {
            // Create the Function
            $function = CityFunction::create([
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'image' => $data['image'],
            ]);

            // Create the Impacts
            foreach ($data['impacts'] as $impactData) {
                if ($impactData['id']) {
                    $impact = new FunctionImpact();
                    $impact->forceFill([
                        'city_function_id' => $function->id,
                        'quality_metric_id' => $impactData['id'],
                        'impact' => $impactData['val'],
                        'condition' => $impactData['cond'] ?? 'always'
                    ]);
                    $impact->save();
                }
            }
        }
    }
}
