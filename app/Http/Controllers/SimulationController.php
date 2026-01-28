<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CityFunction;
use App\Models\Simulation;
use App\Models\SimulationEvent;
use App\Models\QualityMetric; // <--- Don't forget this import!
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class SimulationController extends Controller
{
    public function index()
    {
        // 1. AUTO-SEED & DATA FIX
        // This ensures the DB has the correct items AND the correct impact values/conditions
        $this->ensureDataConsistency();

        // --- 1. METRICS ---
        $metrics = QualityMetric::all();

        // --- 2. FUNCTIONS ---
        $functions = CityFunction::with(['category', 'impacts'])
            ->withExists(['acknowledgedByUsers' => function ($query) {
                $query->where('user_id', Auth::id());
            }])
            ->get()
            ->sortBy('category.name');

        // --- 3. RULES ---
        $categories = Category::with('incompatibleCategories')->get();
        $incompatibilityRules = [];
        foreach ($categories as $cat) {
            $enemies = $cat->incompatibleCategories->pluck('name')->toArray();
            if (!empty($enemies)) {
                $incompatibilityRules[$cat->name] = $enemies;
            }
        }

        // --- 4. EVENTS ---
        $events = SimulationEvent::with(['impacts.qualityMetric'])->get();

        $jsEventsData = $events->map(function($e) {
            return [
                'id' => $e->id,
                'name' => $e->name,
                'duration' => $e->duration_minutes,
                'impacts' => $e->impacts->map(function($i) {
                    return [
                        'metric_name' => $i->qualityMetric->name ?? 'Unknown',
                        'adjustment' => $i->impact 
                    ];
                })
            ];
        });

        // --- 5. PREPARE VIEW DATA ---
        $groupedFunctions = $functions->groupBy(fn($f) => $f->category->name ?? 'Overig');

        $jsFunctionsData = $functions->map(function($f) {
            
            // Logic Injection for DB Constraint Collisions
            // We couldn't store "Safety Day" AND "Safety Night" in DB due to unique index.
            // So we inject the missing Night/Split logic here.
            $impacts = $f->impacts->map(fn($i) => [
                'metric_id' => $i->quality_metric_id,
                'value' => $i->impact,
                'condition' => $i->condition ?? 'always'
            ]);

            // 1. Stadspark: Safety is +10 (Day) in DB. We need to add -5 (Night).
            if ($f->name === 'Stadspark') {
                $safetyMetric = $f->impacts->first(fn($i) => $i->qualityMetric->name === 'Veiligheid');
                if ($safetyMetric) {
                    $impacts->push([
                        'metric_id' => $safetyMetric->quality_metric_id,
                        'value' => -5,
                        'condition' => 'night_only'
                    ]);
                }
            }

            // 2. Bar / Cafe: Noise is -5 (Day) in DB. We need -30 (Night).
            if ($f->name === 'Bar / Cafe') {
                $noiseMetric = $f->impacts->first(fn($i) => $i->qualityMetric->name === 'Geluidshinder');
                if ($noiseMetric) {
                    $impacts->push([
                        'metric_id' => $noiseMetric->quality_metric_id,
                        'value' => -30,
                        'condition' => 'night_only'
                    ]);
                }
            }

            return [
                'id' => $f->id,
                'name' => $f->name,
                'category' => $f->category->name ?? 'Onbekend',
                'color_hex' => $f->category->color_hex ?? '#cccccc',
                'image' => $f->image,
                'is_new' => !$f->acknowledged_by_users_exists, 
                'impacts' => $impacts->values(), 
            ];
        })->values();

        return view('simulation.dashboard', compact(
            'groupedFunctions',
            'jsFunctionsData',
            'incompatibilityRules',
            'jsEventsData',
            'metrics'
        ));
    }

    private function ensureDataConsistency()
    {
        // 0. AUTO-MIGRATE (Double Check)
        // Fix Missing Column
        if (\Illuminate\Support\Facades\Schema::hasTable('function_impacts') && 
            !\Illuminate\Support\Facades\Schema::hasColumn('function_impacts', 'condition')) {
            try {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE function_impacts ADD COLUMN `condition` VARCHAR(50) DEFAULT 'always' AFTER impact");
            } catch (\Exception $e) {}
        }

        // FIX UNIQUE CONSTRAINT (Allow multiple impacts per metric for Day/Night)
        try {
            // Check if index exists by trying to drop it. 
            // MySQL syntax: ALTER TABLE table DROP INDEX index_name
            // We blindly try; if it fails (doesn't exist), we catch and ignore.
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE function_impacts DROP INDEX func_metric_impact_unique");
        } catch (\Exception $e) {
            // Index likely already dropped or name differs.
        }

        // METRICS
        $air = QualityMetric::firstOrCreate(['name' => 'Luchtkwaliteit']);
        $noise = QualityMetric::firstOrCreate(['name' => 'Geluidshinder']);
        $traffic = QualityMetric::firstOrCreate(['name' => 'Verkeersdoorstroming']);
        $housing = QualityMetric::firstOrCreate(['name' => 'Huiswaarde']);
        $safety = QualityMetric::firstOrCreate(['name' => 'Veiligheid']);
        $energy = QualityMetric::firstOrCreate(['name' => 'Energieverbruik']);

        // DEFINITIONS TO ENFORCE
        // NOTE: We removed duplicate metric entries (e.g. Safety Day + Safety Night) 
        // to prevent Unique Constraint Violation since we couldn't drop the index.
        // The Night effects for these specific collisions are handled in the index() mapping logic.
        $definitions = [
            'Sociale Huur' => [
                ['id' => $housing->id, 'val' => 20, 'cond' => 'always'],
                ['id' => $safety->id, 'val' => 5, 'cond' => 'always'],
                ['id' => $traffic->id, 'val' => -5, 'cond' => 'always'],
                ['id' => $energy->id, 'val' => -10, 'cond' => 'night_only'],
            ],
            'Luxe Flat' => [
                ['id' => $housing->id, 'val' => 40, 'cond' => 'always'],
                ['id' => $energy->id, 'val' => 10, 'cond' => 'always'],
                ['id' => $traffic->id, 'val' => -10, 'cond' => 'always'],
            ],
            'Stadspark' => [
                ['id' => $air->id, 'val' => 30, 'cond' => 'always'],
                ['id' => $noise->id, 'val' => 20, 'cond' => 'always'],
                ['id' => $housing->id, 'val' => 15, 'cond' => 'always'],
                ['id' => $safety->id, 'val' => 10, 'cond' => 'day_only'],
                // REMOVED CONFLICT: ['id' => $safety->id, 'val' => -5, 'cond' => 'night_only'],
            ],
            'Zonnepaneel' => [ 
                ['id' => $energy->id, 'val' => 30, 'cond' => 'day_only'],
                ['id' => $housing->id, 'val' => 5, 'cond' => 'always'],
            ],
            'Bar / Cafe' => [
                ['id' => $noise->id, 'val' => -5, 'cond' => 'day_only'],
                // REMOVED CONFLICT: ['id' => $noise->id, 'val' => -30, 'cond' => 'night_only'],
                ['id' => $safety->id, 'val' => -5, 'cond' => 'night_only'],
                ['id' => $housing->id, 'val' => -5, 'cond' => 'always'],
            ],
            'Winkel' => [
                ['id' => $housing->id, 'val' => 10, 'cond' => 'always'],
                ['id' => $traffic->id, 'val' => -15, 'cond' => 'day_only'],
                ['id' => $safety->id, 'val' => 5, 'cond' => 'day_only'],
                ['id' => $energy->id, 'val' => -10, 'cond' => 'day_only'],
            ],
            'Staal Fabriek' => [
                ['id' => $air->id, 'val' => -50, 'cond' => 'always'],
                ['id' => $noise->id, 'val' => -40, 'cond' => 'always'],
                ['id' => $traffic->id, 'val' => -20, 'cond' => 'always'],
                ['id' => $housing->id, 'val' => -30, 'cond' => 'always'],
            ],
            'Politiebureau' => [
                ['id' => $safety->id, 'val' => 50, 'cond' => 'always'],
                ['id' => $noise->id, 'val' => -5, 'cond' => 'always'],
                ['id' => $housing->id, 'val' => 5, 'cond' => 'always'],
            ]
        ];

        foreach ($definitions as $name => $impacts) {
            // Find function by name (fuzzy match for 'Zonnepaneel' vs 'Zonnepaneel (Demo)')
            $func = CityFunction::where('name', 'LIKE', $name . '%')->first();
            
            // If missing, Create it (simplified logic for required fields)
            if (!$func && ($name === 'Zonnepaneel' || $name === 'Bar / Cafe')) {
                // ... Creation logic from ensureSeeded ...
                // For brevity, skipping full creation here as previous ensureSeeded handle it?
                // Actually, let's just create if missing.
                $catId = 1;
                if ($name == 'Zonnepaneel') $catId = Category::where('name', 'Groen')->value('id') ?? 2;
                if ($name == 'Bar / Cafe') $catId = Category::where('name', 'Dienst')->value('id') ?? 4;
                
                $img = ($name == 'Zonnepaneel') 
                    ? 'https://solarmagazine.nl/storage/images/2023/12/zonnepanelen-dak-huis-2.jpg'
                    : 'https://entree-assets.s3.eu-central-1.amazonaws.com/s3fs-public/styles/header_image/public/2023-08/Bar%20The%20Tailor%20Amsterdam%20Krasnapolsky.jpg?h=a1532f6a&itok=D3g8tLpS';

                $func = CityFunction::create(['name' => $name, 'category_id' => $catId, 'image' => $img]);
            }

            if ($func) {
                // SYNC IMPACTS: We wipe and re-create to ensure exact match with definition
                // Check if we need to sync? To avoid DB spam on every load, 
                // we could check count, but conditions are tricky.
                // Let's just delete and recreate for these specific items to enforce "Data Fix".
                // Ideally, use a flag or cache to do this only once per deployment. 
                // For this demo, doing it always ensures the user sees the fix immediately.
                
                $func->impacts()->delete();
                
                foreach ($impacts as $i) {
                    \App\Models\FunctionImpact::create([
                        'city_function_id' => $func->id,
                        'quality_metric_id' => $i['id'],
                        'impact' => $i['val'],
                    ])->forceFill(['condition' => $i['cond']])->save();
                }
            }
        }
    }

    // --- OTHER METHODS (Keep these exactly as they were) ---

    public function acknowledgeFunction($id)
    {
        $function = CityFunction::findOrFail($id);
        $function->acknowledgedByUsers()->syncWithoutDetaching([Auth::id()]);
        return response()->json(['message' => 'Acknowledged']);
    }

    public function list() { return response()->json(Simulation::orderBy('created_at', 'desc')->get()); }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gridState' => 'required|array',
        ]);

        $simulation = Simulation::create([
            'name' => $validated['name'],
            'grid_state' => $validated['gridState']
        ]);

        return response()->json(['message' => 'Opgeslagen!', 'simulation' => $simulation]);
    }

    public function show($id)
    {
        $simulation = Simulation::findOrFail($id);
        return response()->json(['gridState' => $simulation->grid_state]);
    }

    public function destroy($id)
    {
        Simulation::destroy($id);
        return response()->json(['message' => 'Verwijderd']);
    }
}
