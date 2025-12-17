<?php

namespace App\Http\Controllers;

use App\Models\CityFunction;
use Illuminate\Http\Request;

class CityFunctionController extends Controller
{
    public function acknowledge(Request $request, CityFunction $cityFunction)
    {
        $request->user()->acknowledgedCityFunctions()->syncWithoutDetaching([
            $cityFunction->id => ['acknowledged_at' => now()]
        ]);

        return response()->json(['status' => 'acknowledged']);
    }

}
