<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CityFunction;
use App\Models\Simulation;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth; // Import Auth

class SimulationController extends Controller
{
   public function index()
    {
        // 1. Fetch Functions with Category AND check if current user acknowledged them
        // We use 'withExists' to add a boolean 'acknowledged_by_users_exists' to the result
        $functions = CityFunction::with('category')
            ->withExists(['acknowledgedByUsers' => function ($query) {
                $query->where('user_id', Auth::id());
            }])
            ->get()
            ->sortBy('category.name');

        // ... (Step 2 and Compatibility logic remains the same) ...
        $categories = Category::with('incompatibleCategories')->get();
        $incompatibilityRules = [];
        foreach ($categories as $cat) {
            $enemies = $cat->incompatibleCategories->pluck('name')->toArray();
            if (!empty($enemies)) {
                $incompatibilityRules[$cat->name] = $enemies;
            }
        }

        // 3. Prepare Data
        $groupedFunctions = $functions->groupBy(fn($f) => $f->category->name ?? 'Overig');

        $jsFunctionsData = $functions->map(function($f) {
            return [
                'id' => $f->id,
                'name' => $f->name,
                'category' => $f->category->name ?? 'Onbekend',
                'color_hex' => $f->category->color_hex ?? '#cccccc',
                'livability' => $f->livability_number,
                'image' => $f->image,
                // Add the new flag. If it exists in pivot, it's NOT new.
                'is_new' => !$f->acknowledged_by_users_exists, 
            ];
        })->values();

        return view('simulation.dashboard', compact('groupedFunctions', 'jsFunctionsData', 'incompatibilityRules'));
    }

    // New Method: Handle Acknowledgement via AJAX
    public function acknowledgeFunction($id)
    {
        $function = CityFunction::findOrFail($id);
        
        // Attach current user to the function in the pivot table
        // 'syncWithoutDetaching' ensures we don't get duplicate entry errors
        $function->acknowledgedByUsers()->syncWithoutDetaching([Auth::id()]);

        return response()->json(['message' => 'Acknowledged']);
    }

    // ... keep your other methods (list, store, etc.) as they were ...
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
