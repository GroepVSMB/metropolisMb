<?php

namespace Database\Factories;

use App\Models\CityFunction;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CityFunctionFactory extends Factory
{
    protected $model = CityFunction::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'image' => 'test.png',
            'livability_number' => $this->faker->numberBetween(1, 10),
            'category_id' => Category::factory(),
        ];
    }
}
