<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Simulation>
 */
class SimulationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'grid_state' => array_fill(0, 12, 0),
            'grid_width' => 4,
            'grid_height' => 3,
            'grid_type' => 'square',
            'schedule_state' => [],
            'current_tick' => 0,
            'status' => 'paused',
            'speed' => 1,
        ];
    }
}