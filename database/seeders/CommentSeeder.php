<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Zoek de Policy Maker (aangemaakt in DatabaseSeeder)
        // Als die niet bestaat, pakken we de eerste beste user.
        $user = User::where('email', 'policy@test.com')->first() ?? User::first();

        if (!$user) {
            return; // Geen user gevonden, dus stoppen we.
        }

        // 2. Maak een paar comments aan
        
        // Comment op Kavel 2 (Index 1 of 2, afhankelijk van hoe je telt, hier index 2)
        Comment::create([
            'user_id' => $user->id,
            'grid_index' => 2, 
            'content' => 'Hier zou eigenlijk meer groen moeten komen voor de waterafvoer.',
            'is_resolved' => false,
        ]);

        // Comment op Kavel 5 (Opgelost)
        Comment::create([
            'user_id' => $user->id,
            'grid_index' => 5,
            'content' => 'Let op: deze industrie ligt te dicht bij de woonwijk.',
            'is_resolved' => true, // Deze is al groen/afgevinkt
        ]);

        // Comment op Kavel 9
        Comment::create([
            'user_id' => $user->id,
            'grid_index' => 9,
            'content' => 'Kunnen we hier een school plaatsen in plaats van kantoren?',
            'is_resolved' => false,
        ]);
    }
}