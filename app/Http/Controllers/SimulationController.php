<?php

namespace App\Http\Controllers;

use App\Models\CityFunction;
use App\Models\Simulation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SimulationController extends Controller
{
    public function index(): View
    {
        $functions = CityFunction::all();
        return view('simulation.dashboard', compact('functions'));
    }

    // READ (List all saved layouts)
    public function list()
    {
        return response()->json(Simulation::orderBy('created_at', 'desc')->get());
    }

    // CREATE (Save a new layout)
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

        return response()->json([
            'message' => 'Opgeslagen!',
            'simulation' => $simulation
        ]);
    }

    // READ (Load a specific layout)
    public function show($id)
    {
        $simulation = Simulation::findOrFail($id);
        return response()->json(['gridState' => $simulation->grid_state]);
    }

    // DELETE
    public function destroy($id)
    {
        Simulation::destroy($id);
        return response()->json(['message' => 'Verwijderd']);
    }
}
