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
        // 1. Fetch existing metrics (Make sure names match your QualityMetricSeeder!)
        $noise   = QualityMetric::where('name', 'Geluidshinder')->first();
        $traffic = QualityMetric::where('name', 'Verkeersdoorstroming')->first();
        $air     = QualityMetric::where('name', 'Luchtkwaliteit')->first();
        $safety  = QualityMetric::where('name', 'Veiligheid')->first();

        // Safety check: if metrics are missing, stop to prevent crash
        if (!$noise || !$traffic || !$air || !$safety) {
            $this->command->warn("Some metrics not found. Skipping Event Impacts.");
            return;
        }

        // --- EVENT 1: Music Festival ---
        $festival = SimulationEvent::create([
            'name' => 'Music Festival',
            'type' => 'one_off',
            'duration_minutes' => 120,
        ]);

        EventImpact::create([
            'simulation_event_id' => $festival->id,
            'quality_metric_id' => $noise->id,
            'impact' => -30 // Loud noise
        ]);

        EventImpact::create([
            'simulation_event_id' => $festival->id,
            'quality_metric_id' => $traffic->id,
            'impact' => -10 // Traffic jams
        ]);

        // --- EVENT 2: Car Free Sunday ---
        $carFree = SimulationEvent::create([
            'name' => 'Car Free Sunday',
            'type' => 'recurring',
            'duration_minutes' => 600,
            'recurrence_interval_minutes' => 10080, // Weekly
        ]);

        EventImpact::create([
            'simulation_event_id' => $carFree->id,
            'quality_metric_id' => $air->id,
            'impact' => 20 // Better air
        ]);

        EventImpact::create([
            'simulation_event_id' => $carFree->id,
            'quality_metric_id' => $safety->id,
            'impact' => 10 // Safer streets
        ]);
    }
}
