<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QualityMetricSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \App\Models\QualityMetric::create(['name' => 'Air Quality']);
        \App\Models\QualityMetric::create(['name' => 'Noise Pollution']); // Note: Negative impact usually good here? Or keep consistent (Positive = Good)
        \App\Models\QualityMetric::create(['name' => 'Traffic Flow']);
        \App\Models\QualityMetric::create(['name' => 'Housing Value']);
        \App\Models\QualityMetric::create(['name' => 'Safety']);
    }
}
