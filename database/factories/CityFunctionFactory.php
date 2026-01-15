<?php

namespace Database\Factories;

use App\Models\CityFunction;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CityFunctionFactory extends Factory
{
    protected $model = CityFunction::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'category_id' => Category::factory(),
            'livability_number' => $this->faker->numberBetween(1, 10),
            'image' => $this->faker->imageUrl(),
        ];
    }
}
