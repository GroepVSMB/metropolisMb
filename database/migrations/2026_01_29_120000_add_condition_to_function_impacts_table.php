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
        Schema::table('function_impacts', function (Blueprint $table) {
            // 'always', 'day_only', 'night_only'
            $table->string('condition')->default('always')->after('impact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('function_impacts', function (Blueprint $table) {
            $table->dropColumn('condition');
        });
    }
};
