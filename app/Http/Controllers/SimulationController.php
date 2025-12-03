<?php

namespace App\Http\Controllers;

use App\Models\CityFunction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SimulationController extends Controller
{
    public function index(): View
    {
        // Fetch functions for the Library sidebar
        $functions = CityFunction::all();

        return view('simulation.dashboard', compact('functions'));
    }
}
