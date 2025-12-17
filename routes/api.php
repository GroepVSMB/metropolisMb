<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:planner'])->post(
    '/city-functions/{cityFunction}/acknowledge',
    [CityFunctionController::class, 'acknowledge']
);