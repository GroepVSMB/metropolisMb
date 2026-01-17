<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('simulations', function (Blueprint $table) {
            $table->unsignedBigInteger('current_tick')->default(0)->after('grid_state');
            $table->string('status')->default('paused')->after('current_tick'); // paused, playing
            $table->integer('speed')->default(1)->after('status');
        });

        Schema::table('simulation_events', function (Blueprint $table) {
            $table->foreignId('simulation_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->integer('start_minute')->default(0)->after('recurrence_interval_minutes');
            $table->boolean('is_active')->default(true)->after('start_minute'); // User toggle to enable/disable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simulation_events', function (Blueprint $table) {
            $table->dropForeign(['simulation_id']);
            $table->dropColumn(['simulation_id', 'start_minute', 'is_active']);
        });

        Schema::table('simulations', function (Blueprint $table) {
            $table->dropColumn(['current_tick', 'status', 'speed']);
        });
    }
};