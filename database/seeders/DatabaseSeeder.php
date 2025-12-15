<?php

namespace Database\Seeders;

use App\Models\CityFunction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; 

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Maak de Manager
        User::factory()->create([
            'name' => 'De Manager',
            'email' => 'manager@test.com',
            'role' => 'manager',
            'password' => Hash::make('wachtwoord'),
        ]);

        // Maak de Planner
        User::factory()->create([
            'name' => 'De Planner',
            'email' => 'planner@test.com',
            'role' => 'planner',
            'password' => Hash::make('wachtwoord'),
            ]);
        // User::factory(10)->create();
        $this->call([
            CategorySeeder::class,
            CityFunctionSeeder::class
        ]);
    }
}
