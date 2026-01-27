<?php

use App\Http\Controllers\LibraryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\RuleController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CommentController;
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

    Route::get('/library/matrix', [LibraryController::class, 'effectsMatrix'])->name('library.matrix');
    Route::post('/library/matrix', [LibraryController::class, 'updateEffectsMatrix'])->name('library.matrix.update');
});


// 4. Simulation & Shared Routes (Planner & Policy Maker)
// We geven hier TWEE rollen mee (gescheiden door komma). 
// Dit betekent: Als je Planner BENT OF Policy Maker BENT, mag je erin.
Route::middleware(['auth', 'role:planner,policy_maker'])->group(function () {
    
    // De hoofd-simulatie pagina (Dashboard)
    Route::get('/simulation', [SimulationController::class, 'index'])->name('simulation.dashboard');
    
    // Het wegklikken van "Nieuw" notificaties (geldt voor beide)
    Route::post('/simulation/acknowledge/{id}', [SimulationController::class, 'acknowledgeFunction'])->name('simulation.acknowledge');
 Route::get('/comments', [CommentController::class, 'index']);
    Route::post('/comments', [CommentController::class, 'store']);
     Route::post('/comments/{comment}/resolve', [CommentController::class, 'resolve']);
});

// 5. Planner Specific Routes (Alleen Planner)
Route::middleware(['auth', 'role:planner'])->group(function () {
    // Events beheren mag alleen de planner
    Route::resource('events', EventController::class);
});

// 6. Policy Maker Specific Routes (Alleen Policy Maker)
Route::middleware(['auth', 'role:policy_maker'])->group(function () {
    // Comment Routes (API endpoints voor opslaan/verwijderen)
   
    
    // Let op: Resolven mag vaak door beiden, maar verwijderen alleen door eigenaar. 
    // We laten de routes hier open staan, de controller checkt eigenaarschap.
       
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
});


// 5. Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // === NIEUW: Notification Routes ===
    // Deze moeten voor iedereen beschikbaar zijn die ingelogd is
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all', [NotificationController::class, 'markAllRead'])->name('notifications.markAll');
});



require __DIR__.'/auth.php';
