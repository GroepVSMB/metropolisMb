<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_simulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('grid_state'); // Store grid state as JSON
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_simulations');
    }
};
