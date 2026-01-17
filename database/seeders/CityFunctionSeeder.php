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
        $infrastructuur = Category::where('name', 'Infrastructuur')->first()->id ?? 6;

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
                'name' => 'Weg',
                'category_id' => $infrastructuur,
                'image' => null, // Simple color block or find a road texture
                'impacts' => [
                    $traffic->id => 20,  // Improves flow
                    $noise->id => -10,   // Adds noise
                    $air->id => -5,      // Slight pollution
                ]
            ],
            [
                'name' => 'Sociale Huur',
                'category_id' => $wonen,
                'image' => 'https://finance-ideas.nl/wp-content/uploads/2022/07/wanneer-is-een-woning-een-sociale-huurwoning.jpg',
                'impacts' => [
                    $housing->id => 20,  // Good for housing availability
                    $safety->id => 5,    // Social cohesion
                    $traffic->id => -5,  // Slight traffic increase
                ]
            ],
            [
                'name' => 'Luxe Flat',
                'category_id' => $wonen,
                'image' => 'https://www.bouwenmetnatuursteen.nl/wp-content/uploads/2024/03/NG-Bouwen-met-Natuursteen-Luxe-parkappartementen-1-Noordwijk-Constanta1.jpg',
                'impacts' => [
                    $housing->id => 40,  // High value
                    $energy->id => 10,   // Modern insulation
                    $traffic->id => -10, // More cars
                ]
            ],
            [
                'name' => 'Stadspark',
                'category_id' => $groen,
                'image' => 'https://assets.plaece.nl/thumb/Bid1aB6mtgfI8CjAizLbj5hKJ9JC59RqChfhHnMlYBg/resizing_type:fit/width:960/height:0/gravity:sm/enlarge:0/aHR0cHM6Ly9hc3NldHMucGxhZWNlLm5sL2t1bWEtZ3JvbmluZ2VuL3VwbG9hZHMvbWVkaWEvNjBlNWI3NjQwNzA1Yi8yNy1sYXJnZS5qcGc.jpg',
                'impacts' => [
                    $air->id => 30,      // Trees clean air
                    $noise->id => 20,    // Absorbs noise (Positive score = Less noise)
                    $housing->id => 15,  // Attractive to live near
                    $safety->id => 10,   // Recreation
                ]
            ],
            [
                'name' => 'Staal Fabriek',
                'category_id' => $werken,
                'image' => 'https://rogierbos.com/wp-content/uploads/2024/10/Industrieel-fotograaf-voor-staal-en-metaal-bij-Hoogovens-TataSteel-5.jpg',
                'impacts' => [
                    $air->id => -50,     // Heavy pollution
                    $noise->id => -40,   // Very loud
                    $traffic->id => -20, // Heavy trucks
                    $housing->id => -30, // Nobody wants to live here
                ]
            ],
            [
                'name' => 'Winkel',
                'category_id' => $dienst,
                'image' => 'https://www.mallatmillenia.com/wp-content/uploads/2025/07/071125_GUCCI_MILLENIA_24_151_v1_QC_R150_1999x1495_acf_cropped.jpg',
                'impacts' => [
                    $housing->id => 10,  // Convenience
                    $traffic->id => -15, // Shoppers cause traffic
                    $safety->id => 5,    // Eyes on the street
                ]
            ],
            [
                'name' => 'Politiebureau',
                'category_id' => $veiligheid,
                'image' => 'https://www.galjema.nl/wp-content/uploads/2024/03/Mitchell-van-Eijk_Politiebureau_Oost-Zeeburg-4-15klein.jpg',
                'impacts' => [
                    $safety->id => 50,   // Huge safety boost
                    $noise->id => -5,    // Sirens
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
