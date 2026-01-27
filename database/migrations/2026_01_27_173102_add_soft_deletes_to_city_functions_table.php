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
        Schema::table('city_functions', function (Blueprint $table) {
            $table->softDeletes(); // Voegt de 'deleted_at' kolom toe
        });
    }

    public function down(): void
    {
        Schema::table('city_functions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
