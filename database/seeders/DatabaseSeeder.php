<?php

namespace Database\Seeders;

use App\Models\CityFunction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserRole;

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
            'role' => UserRole::MANAGER,
            'password' => Hash::make('wachtwoord'),
        ]);

        // Maak de Planner
        User::factory()->create([
            'name' => 'De Planner',
            'email' => 'planner@test.com',
            'role' => UserRole::PLANNER,
            'password' => Hash::make('wachtwoord'),
            ]);

        User::factory()->create([
            'name' => 'De Admin',
            'email' => 'admin@test.com',
            'role' => UserRole::ADMIN,
            'password' => Hash::make('wachtwoord'),
        ]);

        $this->call([
            CategorySeeder::class,
            CityFunctionSeeder::class
        ]);

        $this->call(CategoryIncompatibilitySeeder::class);

        $planner = User::where('role', UserRole::PLANNER)->first();
        $allFunctionIds = DB::table('city_functions')->pluck('id');

        // For demonstration, acknowledge only the first 2 functions
        $acknowledged = $allFunctionIds->take(2);

        $now = now();
        foreach ($acknowledged as $functionId) {
            DB::table('city_function_acknowledgements')->updateOrInsert(
                [
                    'user_id' => $planner->id,
                    'city_function_id' => $functionId,
                ],
                [
                    'acknowledged_at' => $now,
                ]
            );
        }
    }
}
