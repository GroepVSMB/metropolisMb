<?php

namespace App\Http\Controllers;

use App\Models\CityFunction;
use App\Models\Simulation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SimulationController extends Controller
{
    public function index()
    {
        // 1. Fetch data
        $functions = CityFunction::with('category')->get()->sortBy('category.name');

        // 2. Prepare JavaScript Data
        $jsFunctionsData = $functions->map(function($f) {
            return [
                'id' => $f->id,
                'name' => $f->name,
                'category' => $f->category->name ?? 'Onbekend',
                'color_hex' => $f->category->color_hex ?? '#cccccc',
                'livability' => $f->livability_number,
                'image' => $f->image,
            ];
        })->values();

        // 3. Prepare HTML List Data
        $groupedFunctions = $functions->groupBy(fn($f) => $f->category->name ?? 'Overig');

        // 4. Send to the SIMULATION view (This was 'library.index' before)
        return view('simulation.dashboard', compact('jsFunctionsData', 'groupedFunctions'));
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
