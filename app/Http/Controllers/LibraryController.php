<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CityFunction;
use App\Models\QualityMetric;
use App\Models\FunctionImpact;
use Illuminate\Http\Request;

use App\Models\User;
use App\Notifications\NewFunctionAdded;
use Illuminate\Support\Facades\Notification;

class LibraryController extends Controller
{
    public function index()
    {
        $functions = CityFunction::with('category')->get()->sortBy('category.name');

        $groupedFunctions = $functions->groupBy(fn($f) => $f->category->name ?? 'Overig');

        $jsFunctionsData = $functions->map(function($f) {
            return [
                'id' => $f->id,
                'name' => $f->name,
                'category' => $f->category->name ?? 'Onbekend',
                'color_hex' => $f->category->color_hex ?? '#cccccc',
                'livability' => $f->livability_number,
                
                // CHECK if the image is stored in a url or a image file because of changes 
                'image' => str_starts_with($f->image, 'http') 
                            ? $f->image 
                            : asset('storage/' . $f->image),
                            
                'created_at' => $f->created_at,
            ];
        })->values();

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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', 
            'impacts' => 'nullable|array',
        ]);

        // SAVE FILE
        // 'storage/app/public/uploads'
        $imagePath = $request->file('image')->store('uploads', 'public');

        $function = CityFunction::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'image' => $imagePath, //save path
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

        $experts = User::where('role', 'planner')->get(); 
        Notification::send($experts, new NewFunctionAdded($function));

        return redirect()->route('library.manage')->with('success', 'Functie met afbeelding toegevoegd!');
    }

    // 5. MANAGER: Update Data (Updated for Links)
    public function update(Request $request, $id)
    {
        $function = CityFunction::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'impacts' => 'nullable|array',
        ]);

        //prepare data
        $data = [
            'name' => $request->name,
            'category_id' => $request->category_id,
        ];

        // CHECK: is there a new image uploaded
        if ($request->hasFile('image')) {
            if ($function->image && !str_starts_with($function->image, 'http')) {
                Storage::disk('public')->delete($function->image);
            }

            // save image
            $data['image'] = $request->file('image')->store('uploads', 'public');
        }

        $function->update($data);

        // Sync Impacts
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
