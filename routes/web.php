<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;
use App\Models\CityFunction;


// 1. Homepage stuurt direct door naar login
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. De standaard Dashboard, Simulation en Library routes
// We groeperen ze zodat ze allemaal beveiligd zijn met 'auth' en 'verified'
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

  // 1. IEDEREEN (Planner & Manager) mag de lijst ZIEN
    Route::get('/library', function () {
        // We halen alle functies op, gesorteerd op categorie
        $functions = CityFunction::with('category')->get()->sortBy('category.name');
        return view('library.index', compact('functions'));
    })->name('library.index');

});

    // 2. ALLEEN MANAGER mag functies toevoegen, bewerken of verwijderen
    Route::middleware(['auth', 'role:manager'])->group(function () {
    
    // Voorbeeld routes voor CRUD (Create, Update, Delete)
    Route::get('/library/create', function() { return "Pagina om toe te voegen"; })->name('library.create');
    Route::post('/library', function() { /* opslaan logica */ })->name('library.store');
    Route::delete('/library/{id}', function($id) { 
        CityFunction::destroy($id); 
        return back(); 
    })->name('library.destroy');
});

// 3. Profiel beheer routes (standaard Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 4. Specifieke Manager routes (beveiligd met jouw RoleMiddleware)
Route::middleware(['auth', 'role:manager'])->group(function () {
    Route::get('/manager/dashboard', function () {
        return "Dit is het Manager Dashboard (alleen voor managers)";
    })->name('manager.dashboard');
});

// 5. Specifieke Planner routes (beveiligd met jouw RoleMiddleware)
Route::middleware(['auth', 'role:planner'])->group(function () {
    Route::get('/planner/agenda', function () {
        return "Dit is de Planner Agenda (alleen voor planners)";
    })->name('planner.agenda');
});

require __DIR__.'/auth.php';