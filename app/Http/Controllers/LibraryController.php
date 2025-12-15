<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CityFunction;
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
        $functions = CityFunction::with('category')->get();
        return view('library.manage', compact('functions'));
    }

    // 2. MANAGER: Show Create Form
    public function create()
    {
        $categories = Category::all();
        return view('library.create', compact('categories'));
    }
// 3. MANAGER: Store Data (Updated for Links)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'livability_number' => 'required|integer',
            'image' => 'required|url', // Must be a valid http/https link
        ]);

        CityFunction::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'livability_number' => $request->livability_number,
            'image' => $request->image, // Save the link directly
        ]);

        return redirect()->route('library.manage')->with('success', 'Functie toegevoegd!');
    }

// 5. MANAGER: Update Data (Updated for Links)
    public function update(Request $request, $id)
    {
        $function = CityFunction::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'livability_number' => 'required|integer',
            'image' => 'required|url',
        ]);

        $function->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'livability_number' => $request->livability_number,
            'image' => $request->image,
        ]);

        return redirect()->route('library.manage')->with('success', 'Functie bijgewerkt!');
    }

    // 4. MANAGER: Show Edit Form
    public function edit($id)
    {
        $function = CityFunction::findOrFail($id);
        $categories = Category::all();
        return view('library.edit', compact('function', 'categories'));
    }

    // 6. MANAGER: Delete
    public function destroy($id)
    {
        $function = CityFunction::findOrFail($id);
        $function->delete();
        return back()->with('success', 'Functie verwijderd.');
    }
}
