<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Simulation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class SimulationLoadTest extends TestCase
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    public function test_can_list_simulations()
    {
        Simulation::query()->delete();
        $planner = User::factory()->create(['role' => 'planner']);
        Simulation::factory()->count(3)->create();

        $response = $this->actingAs($planner)->get(route('simulation.list'));

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_can_load_simulation_details()
    {
        $planner = User::factory()->create(['role' => 'planner']);
        $simulation = Simulation::create([
            'name' => 'Load Test Sim',
            'grid_state' => [1, 2, 3],
            'schedule_state' => [['id' => 1, 'name' => 'Event A']],
            'current_tick' => 500,
            'status' => 'paused',
            'speed' => 1
        ]);

        $response = $this->actingAs($planner)->get(route('simulation.show', $simulation->id));

        $response->assertStatus(200);
        $response->assertJson([
            'name' => 'Load Test Sim',
            'current_tick' => 500
        ]);
        // Verify JSON casting
        $data = $response->json();
        $this->assertIsArray($data['grid_state']);
        $this->assertIsArray($data['schedule_state']);
        $this->assertEquals('Event A', $data['schedule_state'][0]['name']);
    }
}