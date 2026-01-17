<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SimulationEvent;
use App\Models\QualityMetric;
use App\Models\EventImpact;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class SimulationEventSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign keys to clear old data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('event_impacts')->truncate();
        DB::table('category_simulation_event')->truncate();
        SimulationEvent::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Fetch existing metrics (Dutch names)
        $noise   = QualityMetric::where('name', 'Geluidshinder')->first();
        $traffic = QualityMetric::where('name', 'Verkeersdoorstroming')->first();
        $air     = QualityMetric::where('name', 'Luchtkwaliteit')->first();
        $safety  = QualityMetric::where('name', 'Veiligheid')->first();
        $housing = QualityMetric::where('name', 'Woningwaarde')->first();

        if (!$noise || !$traffic || !$air || !$safety || !$housing) {
            $this->command->warn("Sommige metrieken niet gevonden. Run QualityMetricSeeder eerst.");
            return;
        }

        $catGreen = Category::where('name', 'Groen')->first();
        $catInd   = Category::where('name', 'Industrie')->first();
        $catRes   = Category::where('name', 'Wonen')->first();

        // --- EVENT 1: Muziekfestival (One-off) ---
        // Start: Day 2 (Wednesday) at 18:00 (2 * 1440 + 1080 = 3960)
        $festival = SimulationEvent::create([
            'name' => 'Muziekfestival',
            'type' => 'one_off',
            'duration_minutes' => 180, 
            'start_minute' => 3960, 
        ]);
        if($catGreen) $festival->categories()->attach($catGreen);
        EventImpact::create(['simulation_event_id' => $festival->id, 'quality_metric_id' => $noise->id, 'impact' => -40]);
        EventImpact::create(['simulation_event_id' => $festival->id, 'quality_metric_id' => $traffic->id, 'impact' => -20]);
        EventImpact::create(['simulation_event_id' => $festival->id, 'quality_metric_id' => $housing->id, 'impact' => 10]); 

        // --- EVENT 2: Autovrije Zondag (Weekly) ---
        // Start: Day 6 (Sunday) at 08:00 (6 * 1440 + 480 = 9120)
        // Cycle: 10080 (1 Week). Gap = 10080 - 720 = 9360
        $carFree = SimulationEvent::create([
            'name' => 'Autovrije Zondag',
            'type' => 'recurring',
            'duration_minutes' => 720, 
            'recurrence_interval_minutes' => 9360, 
            'start_minute' => 9120,
        ]);
        EventImpact::create(['simulation_event_id' => $carFree->id, 'quality_metric_id' => $air->id, 'impact' => 30]);
        EventImpact::create(['simulation_event_id' => $carFree->id, 'quality_metric_id' => $safety->id, 'impact' => 20]);
        EventImpact::create(['simulation_event_id' => $carFree->id, 'quality_metric_id' => $traffic->id, 'impact' => 10]);

        // --- EVENT 3: Ochtendspits (Daily) ---
        // Start: 08:00 (480). Duration 120 (2h). Cycle 1440. Gap = 1320.
        $morningRush = SimulationEvent::create([
            'name' => 'Ochtendspits',
            'type' => 'recurring',
            'duration_minutes' => 120, 
            'recurrence_interval_minutes' => 1320, 
            'start_minute' => 480,
        ]);
        EventImpact::create(['simulation_event_id' => $morningRush->id, 'quality_metric_id' => $traffic->id, 'impact' => -50]);
        EventImpact::create(['simulation_event_id' => $morningRush->id, 'quality_metric_id' => $air->id, 'impact' => -20]);
        EventImpact::create(['simulation_event_id' => $morningRush->id, 'quality_metric_id' => $noise->id, 'impact' => -15]);

        // --- EVENT 4: Avondspits (Daily) ---
        // Start: 17:00 (1020). Duration 180 (3h). Cycle 1440. Gap = 1260.
        $eveningRush = SimulationEvent::create([
            'name' => 'Avondspits',
            'type' => 'recurring',
            'duration_minutes' => 180, 
            'recurrence_interval_minutes' => 1260, 
            'start_minute' => 1020,
        ]);
        EventImpact::create(['simulation_event_id' => $eveningRush->id, 'quality_metric_id' => $traffic->id, 'impact' => -60]);
        EventImpact::create(['simulation_event_id' => $eveningRush->id, 'quality_metric_id' => $air->id, 'impact' => -25]);

        // --- EVENT 5: Boerenmarkt (Weekly) ---
        // Start: Day 5 (Saturday) at 09:00 (5 * 1440 + 540 = 7740)
        // Cycle 1 week.
        $market = SimulationEvent::create([
            'name' => 'Boerenmarkt',
            'type' => 'recurring',
            'duration_minutes' => 300, 
            'recurrence_interval_minutes' => 9780, // 10080 - 300
            'start_minute' => 7740,
        ]);
        if($catGreen) $market->categories()->attach($catGreen); 
        EventImpact::create(['simulation_event_id' => $market->id, 'quality_metric_id' => $housing->id, 'impact' => 15]);
        EventImpact::create(['simulation_event_id' => $market->id, 'quality_metric_id' => $air->id, 'impact' => 5]);
        EventImpact::create(['simulation_event_id' => $market->id, 'quality_metric_id' => $traffic->id, 'impact' => -10]);

        // --- EVENT 6: Industrieel Ongeluk (One-off) ---
        // Start: Day 10 at 14:00.
        $accident = SimulationEvent::create([
            'name' => 'Industrieel Ongeluk',
            'type' => 'one_off',
            'duration_minutes' => 240, 
            'start_minute' => 15240, // 10*1440 + 840
        ]);
        if($catInd) $accident->categories()->attach($catInd);
        EventImpact::create(['simulation_event_id' => $accident->id, 'quality_metric_id' => $safety->id, 'impact' => -50]);
        EventImpact::create(['simulation_event_id' => $accident->id, 'quality_metric_id' => $air->id, 'impact' => -60]);
        EventImpact::create(['simulation_event_id' => $accident->id, 'quality_metric_id' => $housing->id, 'impact' => -30]);

        // --- EVENT 7: Politiepatrouille (Daily) ---
        // Start: 22:00 (1320). Duration 60. Gap 1380.
        $patrol = SimulationEvent::create([
            'name' => 'Politiepatrouille',
            'type' => 'recurring',
            'duration_minutes' => 60, 
            'recurrence_interval_minutes' => 1380, 
            'start_minute' => 1320,
        ]);
        if($catRes) $patrol->categories()->attach($catRes);
        EventImpact::create(['simulation_event_id' => $patrol->id, 'quality_metric_id' => $safety->id, 'impact' => 25]);
        EventImpact::create(['simulation_event_id' => $patrol->id, 'quality_metric_id' => $noise->id, 'impact' => -5]); 
    }
}
