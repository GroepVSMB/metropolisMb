<?php

use App\Http\Controllers\SimulationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SimulationController::class, 'index'])->name('simulation.index');

Route::post('/simulation/save-grid', [SimulationController::class, 'saveGrid'])->name('simulation.saveGrid');
Route::get('/simulation/load-grid', [SimulationController::class, 'loadGrid'])->name('simulation.loadGrid');
