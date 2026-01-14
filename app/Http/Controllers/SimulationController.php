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
        // --- 1. METRICS (NEW) ---
        // Fetch the columns for the score table (Air Quality, Noise, etc.)
        $metrics = QualityMetric::all();

        // --- 2. FUNCTIONS & IMPACTS (UPDATED) ---
        // We now eager load 'impacts' so we know how each function affects the metrics
        $functions = CityFunction::with(['category', 'impacts'])
            ->withExists(['acknowledgedByUsers' => function ($query) {
                $query->where('user_id', Auth::id());
            }])
            ->get()
            ->sortBy('category.name');

        // --- 3. RULES (Existing Logic) ---
        $categories = Category::with('incompatibleCategories')->get();
        $incompatibilityRules = [];
        foreach ($categories as $cat) {
            $enemies = $cat->incompatibleCategories->pluck('name')->toArray();
            if (!empty($enemies)) {
                $incompatibilityRules[$cat->name] = $enemies;
            }
        }

        // --- 4. EVENTS (Existing Logic) ---
        // Note: You might want to update events to use QualityMetrics later too,
        // but for now we keep this to prevent breaking your current event logic.
        $events = SimulationEvent::with(['impacts.qualityMetric'])->get();

        // Map events to a clean JSON structure
        $jsEventsData = $events->map(function($e) {
            return [
                'id' => $e->id,
                'name' => $e->name,
                'duration' => $e->duration_minutes,
                'impacts' => $e->impacts->map(function($i) {
                    return [
                        // Ensure this matches your Event logic (Category vs QualityMetric)
                        'metric_name' => $i->qualityMetric->name ?? 'Unknown',
                        'adjustment' => $i->impact // or livability_adjustment
                    ];
                })
            ];
        });

        // --- 5. PREPARE VIEW DATA (UPDATED) ---
        $groupedFunctions = $functions->groupBy(fn($f) => $f->category->name ?? 'Overig');

        $jsFunctionsData = $functions->map(function($f) {
            return [
                'id' => $f->id,
                'name' => $f->name,
                'category' => $f->category->name ?? 'Onbekend',
                'color_hex' => $f->category->color_hex ?? '#cccccc',
                'image' => $f->image,
                'is_new' => !$f->acknowledged_by_users_exists,
                // NEW: Send the specific impacts map { metric_id: score }
                'impacts' => $f->impacts->pluck('impact', 'quality_metric_id'),
            ];
        })->values();

        // Pass everything to the view, including the new $metrics
        return view('simulation.dashboard', compact(
            'groupedFunctions',
            'jsFunctionsData',
            'incompatibilityRules',
            'jsEventsData',
            'metrics' // <--- Essential for the view loop
        ));
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
