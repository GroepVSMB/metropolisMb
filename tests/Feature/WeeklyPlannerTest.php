<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Simulation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class WeeklyPlannerTest extends TestCase
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    public function test_can_save_simulation_with_schedule()
    {
        $planner = User::factory()->create(['role' => 'planner']);

        $scheduleData = [
            [
                'instance_id' => 123456789,
                'template_id' => 1,
                'day' => 0, // Monday
                'start_time' => 600, // 10:00 AM
                'duration' => 60,
                'name' => 'Market Day'
            ],
            [
                'instance_id' => 987654321,
                'template_id' => 2,
                'day' => 2, // Wednesday
                'start_time' => 840, // 2:00 PM
                'duration' => 120,
                'name' => 'Festival'
            ]
        ];

        $response = $this->actingAs($planner)->postJson(route('simulation.store'), [
            'name' => 'Weekly Plan Test',
            'gridState' => [0,0,0,0],
            'scheduleState' => $scheduleData,
            'currentTick' => 0,
            'status' => 'paused'
        ]);

        $response->assertStatus(200);

        $simulation = Simulation::where('name', 'Weekly Plan Test')->first();
        $this->assertNotNull($simulation);
        $this->assertIsArray($simulation->schedule_state);
        $this->assertCount(2, $simulation->schedule_state);
        $this->assertEquals('Market Day', $simulation->schedule_state[0]['name']);
    }
}