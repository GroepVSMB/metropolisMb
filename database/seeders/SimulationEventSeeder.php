<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SimulationEvent;
use App\Models\QualityMetric;
use App\Models\EventImpact;

class SimulationEventSeeder extends Seeder
{
    public function run()
    {
        // 1. Get Metrics (Make sure QualityMetricSeeder ran first!)
        $noise = QualityMetric::where('name', 'Noise Pollution')->first();
        $air   = QualityMetric::where('name', 'Air Quality')->first();
        $traffic = QualityMetric::where('name', 'Traffic Flow')->first();

        // --- EVENT 1: Music Festival ---
        $festival = SimulationEvent::create([
            'name' => 'Music Festival',
            'type' => 'one_off',
            'duration_minutes' => 120,
        ]);

        if ($noise) {
            EventImpact::create([
                'simulation_event_id' => $festival->id,
                'quality_metric_id' => $noise->id,
                'impact' => -30 // Lots of noise (Negative impact)
            ]);
        }

        if ($traffic) {
            EventImpact::create([
                'simulation_event_id' => $festival->id,
                'quality_metric_id' => $traffic->id,
                'impact' => -10 // Traffic jams
            ]);
        }

        // --- EVENT 2: Car Free Sunday ---
        $carFree = SimulationEvent::create([
            'name' => 'Car Free Sunday',
            'type' => 'recurring',
            'duration_minutes' => 600,
        ]);

        if ($air) {
            EventImpact::create([
                'simulation_event_id' => $carFree->id,
                'quality_metric_id' => $air->id,
                'impact' => 20 // Clean air!
            ]);
        }
    }
}
