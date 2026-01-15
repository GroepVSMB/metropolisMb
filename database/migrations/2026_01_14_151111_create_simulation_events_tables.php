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
            $table->string('name');
            // REMOVED: category_id (We use a pivot table now)
            $table->enum('type', ['one_off', 'recurring'])->default('one_off');
            $table->integer('duration_minutes')->default(60);
            $table->integer('recurrence_interval_minutes')->nullable();
            $table->timestamps();
        });

        // 2. NEW: Pivot Table for Multiple Categories
        Schema::create('category_simulation_event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simulation_event_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // 3. The Specific Impacts (Remains the same)
        Schema::create('event_impacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simulation_event_id')->constrained()->onDelete('cascade');
            $table->foreignId('quality_metric_id')->constrained()->onDelete('cascade');
            $table->integer('impact');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_impacts');
        Schema::dropIfExists('category_simulation_event');
        Schema::dropIfExists('simulation_events');
    }
};
