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
        $events = SimulationEvent::with(['impacts.qualityMetric', 'categories'])->get();

        // Map events to a clean JSON structure
        $jsEventsData = $events->map(function($e) {
            return [
                'id' => $e->id,
                'name' => $e->name,
                'type' => $e->type, // one_off, recurring
                'duration' => $e->duration_minutes,
                'recurrence' => $e->recurrence_interval_minutes,
                'start_minute' => $e->start_minute,
                'categories' => $e->categories->pluck('name')->toArray(), // Array of category names
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
            'scheduleState' => 'nullable|array', // NEW
            'currentTick' => 'nullable|integer',
            'status' => 'nullable|string',
            'speed' => 'nullable|integer'
        ]);

        $simulation = Simulation::create([
            'name' => $validated['name'],
            'grid_state' => $validated['gridState'],
            'schedule_state' => $validated['scheduleState'] ?? [], // NEW
            'current_tick' => $validated['currentTick'] ?? 0,
            'status' => $validated['status'] ?? 'paused',
            'speed' => $validated['speed'] ?? 1
        ]);

        return response()->json(['message' => 'Opgeslagen!', 'simulation' => $simulation]);
    }

    public function update(Request $request, $id)
    {
        $simulation = Simulation::findOrFail($id);
        
        $validated = $request->validate([
            'gridState' => 'sometimes|array',
            'scheduleState' => 'sometimes|array', // NEW
            'currentTick' => 'sometimes|integer',
            'status' => 'sometimes|string',
            'speed' => 'sometimes|integer'
        ]);

        if (isset($validated['gridState'])) $simulation->grid_state = $validated['gridState'];
        if (isset($validated['scheduleState'])) $simulation->schedule_state = $validated['scheduleState']; // NEW
        if (isset($validated['currentTick'])) $simulation->current_tick = $validated['currentTick'];
        if (isset($validated['status'])) $simulation->status = $validated['status'];
        if (isset($validated['speed'])) $simulation->speed = $validated['speed'];
        
        $simulation->save();

        return response()->json(['message' => 'Bijgewerkt!', 'simulation' => $simulation]);
    }

    public function show($id)
    {
        $simulation = Simulation::findOrFail($id);
        return response()->json($simulation);
    }

    public function destroy($id)
    {
        Simulation::destroy($id);
        return response()->json(['message' => 'Verwijderd']);
    }
}
