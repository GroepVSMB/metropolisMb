<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CityFunction;
use App\Models\Simulation;
use App\Models\SimulationEvent; // 1. IMPORT ADDED
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class SimulationController extends Controller
{
    public function index()
    {
        // --- 1. FUNCTIONS & ACKNOWLEDGEMENTS (Existing Logic) ---
        $functions = CityFunction::with('category')
            ->withExists(['acknowledgedByUsers' => function ($query) {
                $query->where('user_id', Auth::id());
            }])
            ->get()
            ->sortBy('category.name');

        // --- 2. RULES (Existing Logic) ---
        $categories = Category::with('incompatibleCategories')->get();
        $incompatibilityRules = [];
        foreach ($categories as $cat) {
            $enemies = $cat->incompatibleCategories->pluck('name')->toArray();
            if (!empty($enemies)) {
                $incompatibilityRules[$cat->name] = $enemies;
            }
        }

        // --- 3. EVENTS (NEW LOGIC FOR SIM.4) ---
        // Fetch events and eagerly load impacts + category names
        $events = SimulationEvent::with(['impacts.category'])->get();

        // Map events to a clean JSON structure for the frontend
        $jsEventsData = $events->map(function($e) {
            return [
                'id' => $e->id,
                'name' => $e->name,
                'duration' => $e->duration_minutes,
                'impacts' => $e->impacts->map(function($i) {
                    return [
                        'category_name' => $i->category->name ?? 'Unknown',
                        'adjustment' => $i->livability_adjustment
                    ];
                })
            ];
        });

        // --- 4. PREPARE VIEW DATA ---
        $groupedFunctions = $functions->groupBy(fn($f) => $f->category->name ?? 'Overig');

        $jsFunctionsData = $functions->map(function($f) {
            return [
                'id' => $f->id,
                'name' => $f->name,
                'category' => $f->category->name ?? 'Onbekend',
                'color_hex' => $f->category->color_hex ?? '#cccccc',
                'livability' => $f->livability_number,
                'image' => $f->image,
                'is_new' => !$f->acknowledged_by_users_exists, 
            ];
        })->values();

        // Pass 'jsEventsData' to the view
        return view('simulation.dashboard', compact(
            'groupedFunctions', 
            'jsFunctionsData', 
            'incompatibilityRules', 
            'jsEventsData'
        ));
    }

    // --- OTHER METHODS (Unchanged) ---

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