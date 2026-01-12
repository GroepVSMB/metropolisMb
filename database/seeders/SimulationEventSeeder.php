<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SimulationEvent;
use App\Models\Category;
use App\Models\EventImpact;

class SimulationEventSeeder extends Seeder
{
    public function run()
    {
        // 1. Haal Categorieën op (Pas de namen aan aan jouw database!)
        // Als je niet zeker bent, kun je ook Category::find(1) gebruiken.
        $catGroen = Category::where('name', 'Groen')->first() ?? Category::first();
        $catWonen = Category::where('name', 'Wonen')->first() ?? Category::latest()->first();

        if (!$catGroen) {
            $this->command->error('Geen categorieën gevonden. Voeg eerst categorieën toe.');
            return;
        }

        // --- EVENT 1: Zomerfestival ---
        $festival = SimulationEvent::create([
            'name' => 'Zomerfestival',
            'type' => 'one_off',
            'duration_minutes' => 5, // Kort voor demo
        ]);

        // Impact: Groen wordt leuker (+20), maar Wonen heeft last van geluid (-15)
        EventImpact::create([
            'simulation_event_id' => $festival->id,
            'category_id' => $catGroen->id,
            'livability_adjustment' => 20
        ]);
        
        if ($catWonen) {
            EventImpact::create([
                'simulation_event_id' => $festival->id,
                'category_id' => $catWonen->id,
                'livability_adjustment' => -15
            ]);
        }

        // --- EVENT 2: Hittegolf ---
        $heatwave = SimulationEvent::create([
            'name' => 'Hittegolf',
            'type' => 'recurring',
            'duration_minutes' => 10,
        ]);

        // Impact: Groen is essentieel (+50), Wonen is onprettig (-10)
        EventImpact::create([
            'simulation_event_id' => $heatwave->id,
            'category_id' => $catGroen->id,
            'livability_adjustment' => 50
        ]);
        
        if ($catWonen) {
            EventImpact::create([
                'simulation_event_id' => $heatwave->id,
                'category_id' => $catWonen->id,
                'livability_adjustment' => -10
            ]);
        }
    }
}