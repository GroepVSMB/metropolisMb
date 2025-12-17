<?php

use App\Http\Controllers\LibraryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\RuleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Homepage Redirect (Cleaner syntax)
Route::redirect('/', '/login');

// 2. Authenticated Routes (Dashboard & Simulation)
Route::middleware(['auth', 'verified'])->group(function () {

    // Use Route::view for pages that don't need logic
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    // Library Read-Only (Everyone)
    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
});

// 3. Manager Routes (CRUD & Dashboard)
Route::middleware(['auth', 'verified', 'role:manager'])->group(function () {

    Route::get('/manager/dashboard', function () {
        return view('dashboard'); // Or use a controller if you have one
    })->name('manager.dashboard');
    // The Manager's Table View
    Route::get('/library/manage', [LibraryController::class, 'manage'])->name('library.manage');

    // Create
    Route::get('/library/create', [LibraryController::class, 'create'])->name('library.create');
    Route::post('/library', [LibraryController::class, 'store'])->name('library.store');

    // Edit
    Route::get('/library/{id}/edit', [LibraryController::class, 'edit'])->name('library.edit');
    Route::put('/library/{id}', [LibraryController::class, 'update'])->name('library.update');

    // Delete
    Route::delete('/library/{id}', [LibraryController::class, 'destroy'])->name('library.destroy');

    // Read
    Route::get('/manager/rules', [RuleController::class, 'index'])->name('adjacency.index');

    // Create
    Route::get('/manager/create', [RuleController::class, 'create'])->name('adjacency.create');
    Route::post('/manager', [RuleController::class, 'store'])->name('adjacency.store');

    // Edit
    Route::get('/manager/rules/{id}/edit', [RuleController::class, 'edit'])->name('adjacency.edit');
    Route::put('/manager/rules/{id}', [RuleController::class, 'update'])->name('adjacency.update');

    // Delete
    Route::delete('/manager/{id}', [RuleController::class, 'destroy'])->name('adjacency.destroy');
});

// 4. Planner Routes
Route::middleware(['auth', 'role:planner'])->group(function () {
    // If you haven't created PlannerController yet, change this back to a closure.
    Route::get('/simulation', [SimulationController::class, 'index'])->name('simulation.dashboard');
    // NEW: Acknowledgement Route
    Route::post('/simulation/acknowledge/{id}', [SimulationController::class, 'acknowledgeFunction'])->name('simulation.acknowledge');
});

// 5. Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
