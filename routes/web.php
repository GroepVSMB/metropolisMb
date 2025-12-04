<?php

use App\Http\Controllers\SimulationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SimulationController::class, 'index']);
Route::post('/simulation/save', [SimulationController::class, 'store']);   // Create
Route::get('/simulation/list', [SimulationController::class, 'list']);     // Read (List)
Route::get('/simulation/{id}', [SimulationController::class, 'show']);     // Read (Single)
Route::delete('/simulation/{id}', [SimulationController::class, 'destroy']); // Delete
