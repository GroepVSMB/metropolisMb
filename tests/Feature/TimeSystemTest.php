<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Simulation;
use App\Models\SimulationEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeSystemTest extends TestCase
{
    // use RefreshDatabase; // Commented out to avoid wiping existing DB if using local file, but recommended for tests. 
    // Since I see other tests failing, I'll avoid RefreshDatabase to not mess up if there is a persistent DB the user cares about, 
    // although standard practice is to use it. Given the "metropolisMb" name, it might be a specific project. 
    // Actually, `php artisan test` usually uses sqlite in memory or a separate DB. 
    // I will use DatabaseTransactions to be safe.
    use \Illuminate\Foundation\Testing\DatabaseTransactions;
    use \Illuminate\Foundation\Testing\WithoutMiddleware;

    public function test_simulation_can_store_time_data()
    {
        $planner = User::factory()->create(['role' => 'planner']);

        $response = $this->actingAs($planner)->postJson(route('simulation.store'), [
            'name' => 'Test Simulation',
            'gridState' => [0,0,0,0],
            'currentTick' => 120, // 2 hours
            'status' => 'playing',
            'speed' => 2
        ]);

        $response->assertStatus(200);
        
        $simulation = Simulation::where('name', 'Test Simulation')->first();
        $this->assertNotNull($simulation);
        $this->assertEquals(120, $simulation->current_tick);
        $this->assertEquals('playing', $simulation->status);
        $this->assertEquals(2, $simulation->speed);
    }

    public function test_simulation_can_update_time_data()
    {
        $planner = User::factory()->create(['role' => 'planner']);
        $simulation = Simulation::create([
            'name' => 'Old Sim',
            'grid_state' => [0],
            'current_tick' => 0,
            'status' => 'paused',
            'speed' => 1
        ]);

        $response = $this->actingAs($planner)->putJson(route('simulation.update', $simulation->id), [
            'currentTick' => 500,
            'status' => 'paused',
            'speed' => 5
        ]);

        $response->assertStatus(200);

        $simulation->refresh();
        $this->assertEquals(500, $simulation->current_tick);
        $this->assertEquals(5, $simulation->speed);
    }

    public function test_timeline_scrubbing()
    {
        $planner = User::factory()->create(['role' => 'planner']);
        $simulation = Simulation::create([
            'name' => 'Timeline Sim',
            'grid_state' => [0],
            'current_tick' => 100,
            'status' => 'paused',
            'speed' => 1
        ]);

        // Simulate "Scrubbing" by sending a PUT request with a new tick value
        $response = $this->actingAs($planner)->putJson(route('simulation.update', $simulation->id), [
            'currentTick' => 720, // Jump to 12:00
            'status' => 'paused'
        ]);

        $response->assertStatus(200);

        $simulation->refresh();
        $this->assertEquals(720, $simulation->current_tick);
    }
}