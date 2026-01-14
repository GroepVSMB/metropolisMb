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
            $table->enum('type', ['one_off', 'recurring'])->default('one_off');
            $table->integer('duration_minutes')->default(60);
            $table->integer('recurrence_interval_minutes')->nullable();
            $table->timestamps();
        });

        // 2. The Specific Impacts
        // CHANGED: Now links to quality_metrics, just like the Matrix
        Schema::create('event_impacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simulation_event_id')->constrained()->onDelete('cascade');

            // Replaced category_id with quality_metric_id
            $table->foreignId('quality_metric_id')->constrained()->onDelete('cascade');

            $table->integer('impact'); // The +/- score
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_impacts');
        Schema::dropIfExists('simulation_events');
    }
};
