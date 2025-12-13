<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class CityFunctionSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('city_functions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $wonen = Category::where('name', 'Wonen')->first()->id;
        $groen = Category::where('name', 'Groen')->first()->id;
        $werken = Category::where('name', 'Industrie')->first()->id;
        $dienst = Category::where('name', 'Dienst')->first()->id;
        $veiligheid = Category::where('name', 'Veiligheid')->first()->id;

        // Using placeholder images for demonstration
        DB::table('city_functions')->insert([
            [
                'name' => 'Sociale Huur',
                'category_id' => $wonen,
                'livability_number' => 100,
                'image' => 'https://finance-ideas.nl/wp-content/uploads/2022/07/wanneer-is-een-woning-een-sociale-huurwoning.jpg',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Luxe Flat',
                'category_id' => $wonen,
                'livability_number' => 120,
                'image' => 'https://www.bouwenmetnatuursteen.nl/wp-content/uploads/2024/03/NG-Bouwen-met-Natuursteen-Luxe-parkappartementen-1-Noordwijk-Constanta1.jpg',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Stadspark',
                'category_id' => $groen,
                'livability_number' => 150,
                'image' => 'https://assets.plaece.nl/thumb/Bid1aB6mtgfI8CjAizLbj5hKJ9JC59RqChfhHnMlYBg/resizing_type:fit/width:960/height:0/gravity:sm/enlarge:0/aHR0cHM6Ly9hc3NldHMucGxhZWNlLm5sL2t1bWEtZ3JvbmluZ2VuL3VwbG9hZHMvbWVkaWEvNjBlNWI3NjQwNzA1Yi8yNy1sYXJnZS5qcGc.jpg',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Staal Fabriek',
                'category_id' => $werken,
                'livability_number' => 50,
                'image' => 'https://rogierbos.com/wp-content/uploads/2024/10/Industrieel-fotograaf-voor-staal-en-metaal-bij-Hoogovens-TataSteel-5.jpg',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Winkel',
                'category_id' => $dienst,
                'livability_number' => 80,
                'image' => 'https://www.mallatmillenia.com/wp-content/uploads/2025/07/071125_GUCCI_MILLENIA_24_151_v1_QC_R150_1999x1495_acf_cropped.jpg',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'name' => 'Politiebureau',
                'category_id' => $veiligheid,
                'livability_number' => 110,
                'image' => 'https://www.galjema.nl/wp-content/uploads/2024/03/Mitchell-van-Eijk_Politiebureau_Oost-Zeeburg-4-15klein.jpg',
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);
    }
}