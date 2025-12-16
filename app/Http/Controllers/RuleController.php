<?php

namespace App\Http\Controllers;
use App\Models\CategoryIncompatibility;
use App\Models\Category;
use Illuminate\Http\Request;

class RuleController extends Controller
{
    //
    public function index()
    {
        $rules = CategoryIncompatibility::with(['category', 'incompatibleCategory'])->get();
        $categories = Category::all();

        return view('adjacency.index', compact('rules', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('adjacency.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|different:incompatible_category_id',
            'incompatible_category_id' => 'required',
        ],
        [
            'category_id.different' => 'De geselecteerde categorie mag niet hetzelfde zijn als de incompatibele categorie.'
        ]);

        CategoryIncompatibility::create([
            'category_id' => $request->category_id,
            'incompatible_category_id' => $request->incompatible_category_id,
        ]);

        return redirect()->route('adjacency.index')->with('success', 'Incompatibele categorie toegevoegd!');
    }

    public function edit($id)
    {
        $rule = CategoryIncompatibility::findOrFail($id);
        $categories = Category::all(); // Alle categorieën ophalen

        return view('adjacency.edit', compact('rule', 'categories'));
    }

    // Update methode (werkt de bestaande regel bij)
    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required|different:incompatible_category_id',
            'incompatible_category_id' => 'required',
        ],
        [
            'category_id.different' => 'De geselecteerde categorie mag niet hetzelfde zijn als de incompatibele categorie.'
        ]);

        $rule = CategoryIncompatibility::findOrFail($id);
        $rule->update([
            'category_id' => $request->category_id,
            'incompatible_category_id' => $request->incompatible_category_id,
        ]);

        return redirect()->route('adjacency.index')->with('success', 'Incompatibele categorie bijgewerkt!');
    }

    public function destroy($id)
    {
        $function = CategoryIncompatibility::findOrFail($id);
        $function->delete();
        return back()->with('success', 'Regel verwijderd.');
    }
}
