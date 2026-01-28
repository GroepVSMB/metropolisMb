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
                    $housing->id => 20,
                    $safety->id => 5,
                    $traffic->id => -5,
                ]
            ],
            [
                'name' => 'Luxe Flat',
                'category_id' => $wonen,
                'image' => 'uploads/luxe_flat.jpg',
                'impacts' => [
                    $housing->id => 40,
                    $energy->id => 10,
                    $traffic->id => -10,
                ]
            ],
            [
                'name' => 'Stadspark',
                'category_id' => $groen,
                'image' => 'uploads/stadspark.jpg',
                'impacts' => [
                    $air->id => 30,
                    $noise->id => 20,
                    $housing->id => 15,
                    $safety->id => 10,
                ]
            ],
            [
                'name' => 'Staal Fabriek',
                'category_id' => $werken,
                'image' => 'uploads/staal_fabriek.jpg',
                'impacts' => [
                    $air->id => -50,
                    $noise->id => -40,
                    $traffic->id => -20,
                    $housing->id => -30,
                ]
            ],
            [
                'name' => 'Winkel',
                'category_id' => $dienst,
                'image' => 'uploads/winkel.jpg',
                'impacts' => [
                    $housing->id => 10,
                    $traffic->id => -15,
                    $safety->id => 5,
                ]
            ],
            [
                'name' => 'Politiebureau',
                'category_id' => $veiligheid,
                'image' => 'uploads/politiebureau.jpg',
                'impacts' => [
                    $safety->id => 50,
                    $noise->id => -5,
                    $housing->id => 5,
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
            foreach ($data['impacts'] as $metricId => $score) {
                if ($metricId) {
                    FunctionImpact::create([
                        'city_function_id' => $function->id,
                        'quality_metric_id' => $metricId,
                        'impact' => $score
                    ]);
                }
            }
        }
    }
}
