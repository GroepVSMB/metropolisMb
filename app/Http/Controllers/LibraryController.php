<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CityFunction;
use App\Models\QualityMetric;
use App\Models\FunctionImpact;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function index()
    {
        // 1. Fetch data
        $functions = CityFunction::with('category')->get()->sortBy('category.name');

        // 2. Group data for the Blade View (HTML)
        $groupedFunctions = $functions->groupBy(fn($f) => $f->category->name ?? 'Overig');

        // 3. Prepare data for the Popup/Modal (JS)
        $jsFunctionsData = $functions->map(function($f) {
            return [
                'id' => $f->id,
                'name' => $f->name,
                'category' => $f->category->name ?? 'Onbekend',
                'color_hex' => $f->category->color_hex ?? '#cccccc',
                'livability' => $f->livability_number,
                'image' => $f->image, // This is now a direct link URL
                'created_at' => $f->created_at,
            ];
        })->values();

        // 4. IMPORTANT: Pass BOTH variables to the view using compact()
        return view('library.index', compact('groupedFunctions', 'jsFunctionsData'));
    }

    // 1. MANAGER: List View (Table)
    public function manage()
    {
        $functions = CityFunction::with(['category', 'impacts.qualityMetric'])->get();
        return view('library.manage', compact('functions'));
    }

    // 2. MANAGER: Show Create Form
    public function create()
    {
        $categories = Category::all();
        $metrics = QualityMetric::all(); // Pass metrics for the input fields
        return view('library.create', compact('categories', 'metrics'));
    }
// 3. MANAGER: Store Data (Updated for Links)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'image' => 'required|url',
            'impacts' => 'nullable|array', // New validation
        ]);

        $function = CityFunction::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'image' => $request->image,
            // 'livability_number' is removed
        ]);

        // Save new impacts
        if ($request->impacts) {
            foreach ($request->impacts as $metricId => $score) {
                if (!is_null($score) && $score != 0) {
                    FunctionImpact::create([
                        'city_function_id' => $function->id,
                        'quality_metric_id' => $metricId,
                        'impact' => $score,
                    ]);
                }
            }
        }

        return redirect()->route('library.manage')->with('success', 'Functie toegevoegd!');
    }

// 5. MANAGER: Update Data (Updated for Links)
    public function update(Request $request, $id)
    {
        $function = CityFunction::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'image' => 'required|url',
            'impacts' => 'nullable|array',
        ]);

        $function->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'image' => $request->image,
        ]);

        // Sync Impacts: Delete all old ones, save new ones
        $function->impacts()->delete();

        if ($request->impacts) {
            foreach ($request->impacts as $metricId => $score) {
                if (!is_null($score) && $score != 0) {
                    FunctionImpact::create([
                        'city_function_id' => $function->id,
                        'quality_metric_id' => $metricId,
                        'impact' => $score,
                    ]);
                }
            }
        }

        return redirect()->route('library.manage')->with('success', 'Functie bijgewerkt!');
    }

    // 4. MANAGER: Show Edit Form
    public function edit($id)
    {
        $function = CityFunction::with('impacts')->findOrFail($id);
        $categories = Category::all();
        $metrics = QualityMetric::all();

        // Helper array to pre-fill inputs: [metric_id => impact_value]
        $currentImpacts = $function->impacts->pluck('impact', 'quality_metric_id')->toArray();

        return view('library.edit', compact('function', 'categories', 'metrics', 'currentImpacts'));
    }

    // 6. MANAGER: Delete
    public function destroy($id)
    {
        $function = CityFunction::findOrFail($id);
        $function->delete();
        return back()->with('success', 'Functie verwijderd.');
    }

    public function effectsMatrix()
    {
        // 1. Fetch Functions
        $functions = CityFunction::with('category')->get()->sortBy('name');

        // 2. Fetch Metrics (This is the missing part causing your error)
        $metrics = QualityMetric::all();

        // 3. Eager load impacts to make it fast
        $functions->load('impacts');

        // 4. Send BOTH variables to the view
        return view('library.matrix', compact('functions', 'metrics'));
    }

    public function updateEffectsMatrix(Request $request)
    {
        $matrix = $request->input('matrix');

        // Check if matrix is empty to prevent errors
        if (!$matrix) {
            return back()->with('error', 'No data to save.');
        }

        foreach ($matrix as $funcId => $metricImpacts) {
            // ENSURE THIS VARIABLE MATCHES: $metricId
            foreach ($metricImpacts as $metricId => $score) {

                if ($score != 0) {
                    \App\Models\FunctionImpact::updateOrCreate(
                    // We use $metricId here
                        ['city_function_id' => $funcId, 'quality_metric_id' => $metricId],
                        ['impact' => $score]
                    );
                } else {
                    // And we use $metricId here
                    \App\Models\FunctionImpact::where('city_function_id', $funcId)
                        ->where('quality_metric_id', $metricId)
                        ->delete();
                }
            }
        }

        return back()->with('success', 'Impact configuration saved.');
    }


}
