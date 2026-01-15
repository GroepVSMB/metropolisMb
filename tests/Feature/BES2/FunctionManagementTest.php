<?php

use App\Models\User;
use App\Models\Category;
use App\Models\CityFunction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/* This document tests the following:
 * Access control
 * Creating
 * Editing
 * Deleting
 * Validation
 * Integration flow
 * /



/* Help function to get a manager user */
function managerUser() {
    return User::factory()->create([
        'role' => 'manager', // make sure your User model has a role field
    ]);
}

/*
 * BES.2 — Backend Access
 */
test('backend function management screen can be rendered', function () {
    $manager = managerUser();

    $response = $this->actingAs($manager)->get('/library/manage');

    $response->assertStatus(200);
});

test('non-managers cannot access function management page', function () {
    $user = User::factory()->create(['role' => 'planner']); // one time use doesn't require aditional function

    $response = $this->actingAs($user)->get('/library/manage');

    $response->assertStatus(403);
});

/*
 * BES.2 — Create Function
 */
test('manager can add a new function', function () {
    $manager = managerUser();
    $category = Category::factory()->create();

    $response = $this->actingAs($manager)->post(route('library.store'), [
        'name' => 'Playground',
        'category_id' => $category->id,
        'livability_number' => 7,
        'image' => 'https://example.com/icon.png',
    ]);

    $response->assertRedirect(route('library.manage'));
    $response->assertSessionHas('success', 'Functie toegevoegd!');

    expect(CityFunction::count())->toBe(1);
});


/*
 * BES.2 — Update Function
 */
test('manager can adjust an existing function', function () {
    $manager = managerUser();
    $category = Category::factory()->create();

    $function = CityFunction::factory()->create([
        'name' => 'Old Name',
        'category_id' => $category->id,
        'livability_number' => 5,
    ]);

    $response = $this->actingAs($manager)->put(route('library.update', $function->id), [
        'name' => 'New Updated Name',
        'category_id' => $category->id,
        'livability_number' => 10,
        'image' => 'https://example.com/new-icon.png',
    ]);

    $response->assertRedirect(route('library.manage'));
    $response->assertSessionHas('success', 'Functie bijgewerkt!');

    $function->refresh();
    expect($function->name)->toBe('New Updated Name');
});

/*
 * BES.2 — Delete Function
 */
test('manager can delete a function', function () {
    $manager = managerUser();
    $category = Category::factory()->create();

    $function = CityFunction::factory()->create([
        'category_id' => $category->id
    ]);

    $response = $this->actingAs($manager)->delete("/library/{$function->id}");

    $response->assertRedirect('/library/manage');
    expect(CityFunction::count())->toBe(0);
});

/*
 * BES.2 — Data Validation
 */
test('function creation fails data validation', function () {
    $manager = managerUser(); // must return a user with role "manager"

    $response = $this->actingAs($manager)->post('/library', [
        'name' => '',
        'category_id' => 999,
        'livability_number' => 'invalid',
        'image' => 'not-a-url',
    ]);

    $response->assertSessionHasErrors([
        'name',
        'category_id',
        'livability_number',
        'image',
    ]);
});

/*
 * BES.2 — Integration flow
 */

test('full function management flow works', function () {
    $manager = managerUser();
    $category = Category::factory()->create();

    // Create
    $create = $this->actingAs($manager)->post('/library', [
        'name' => 'Park',
        'category_id' => $category->id,
        'livability_number' => 5,
        'image' => 'https://example.com/park.png',
    ]);

    $create->assertRedirect('/library/manage');
    $functionId = CityFunction::first()->id;

    // Update
    $update = $this->put("/library/$functionId", [
        'name' => 'Updated Park',
        'category_id' => $category->id,
        'livability_number' => 7,
        'image' => 'https://example.com/park2.png',
    ]);

    $update->assertRedirect('/library/manage');

    // Delete
    $delete = $this->delete("/library/$functionId");
    $delete->assertRedirect('/library/manage');

    expect(CityFunction::count())->toBe(0);
});



