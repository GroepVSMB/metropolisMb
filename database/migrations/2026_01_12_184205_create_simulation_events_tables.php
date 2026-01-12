<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. The Event Definition
        Schema::create('simulation_events', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Festival", "Rush Hour"
            $table->enum('type', ['one_off', 'recurring'])->default('one_off');
            $table->integer('duration_minutes')->default(60); 
            $table->integer('recurrence_interval_minutes')->nullable(); // e.g., every 1440 mins (24h)
            $table->timestamps();
        });

        // 2. The Specific Impacts (e.g., Parks -> Livability +10)
        Schema::create('event_impacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simulation_event_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->integer('livability_adjustment'); // e.g., +10 or -20
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_impacts');
        Schema::dropIfExists('simulation_events');
    }
};