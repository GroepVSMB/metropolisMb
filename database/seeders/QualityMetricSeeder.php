<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QualityMetric;
use Illuminate\Support\Facades\DB;

class QualityMetricSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Disable foreign keys to allow truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        QualityMetric::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Insert Dutch Metrics
        QualityMetric::create(['name' => 'Luchtkwaliteit']);
        QualityMetric::create(['name' => 'Geluidshinder']); 
        QualityMetric::create(['name' => 'Verkeersdoorstroming']);
        QualityMetric::create(['name' => 'Woningwaarde']);
        QualityMetric::create(['name' => 'Veiligheid']);
    }
}