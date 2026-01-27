<?php

namespace Database\Seeders;

use App\Models\CityFunction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserRole;

class DatabaseSeeder extends Seeder
{
   // use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Maak de Manager
        User::factory()->create([
            'name' => 'Manager',
            'email' => 'manager@test.com',
            'role' => UserRole::MANAGER,
            'password' => Hash::make('wachtwoord'),
        ]);

        // Maak de Planner
        User::factory()->create([
            'name' => 'Planner',
            'email' => 'planner@test.com',
            'role' => UserRole::PLANNER,
            'password' => Hash::make('wachtwoord'),
            ]);

        User::factory()->create([
            'name' => 'Planner2',
            'email' => 'planner2@test.com',
            'role' => UserRole::PLANNER,
            'password' => Hash::make('wachtwoord'),
        ]);



        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'role' => UserRole::ADMIN,
            'password' => Hash::make('wachtwoord'),
        ]);


        User::factory()->create([
            'name' => 'Beleidsmaker',
            'email' => 'policy@test.com',
            'role' => UserRole::POLICY_MAKER,
            'password' => Hash::make('wachtwoord'),
        ]);

        $this->call([
            CategorySeeder::class,
            CityFunctionSeeder::class,
            SimulationEventSeeder::class,
            //QualityMetricSeeder::class
            CommentSeeder::class
        ]);

        $this->call(CategoryIncompatibilitySeeder::class);
    }
}
