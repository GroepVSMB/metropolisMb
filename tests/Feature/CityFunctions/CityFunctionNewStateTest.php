<?php

use App\Models\User;
use App\Models\CityFunction;
use App\Models\CityFunctionAcknowledgement;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a city function is new per user', function () {
    // Create two planners
    $plannerA = User::factory()->create(['role' => 'planner']);
    $plannerB = User::factory()->create(['role' => 'planner']);

    // Create a category first (required for CityFunction)
    $category = \App\Models\Category::factory()->create();

    // Create a city function
    $function = CityFunction::factory()->create([
        'category_id' => $category->id,
    ]);

    // Check initial state: both planners see it as new
    $this->actingAs($plannerA)
         ->get('/library') // optional, just to simulate visiting
         ->assertSee($function->name);

    $this->actingAs($plannerB)
         ->get('/library')
         ->assertSee($function->name);

    // Planner A acknowledges the function
    $this->actingAs($plannerA)
         ->post("/city-functions/{$function->id}/acknowledge")
         ->assertStatus(200);

    expect($plannerA->acknowledgedCityFunctions->contains($function->id))->toBeTrue();
    expect($plannerB->acknowledgedCityFunctions->contains($function->id))->toBeFalse();

});
