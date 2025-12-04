<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('city_functions', function (Blueprint $table) {
            $table->id();
            $table->string('name');       // e.g. "Sociale Huur tabel"
            $table->string('category');   // e.g. "Wonen", "Groen"
            $table->string('color_hex');  // Specific house-style color
            $table->string('text_color')->default('#ffffff'); // Contrast text color
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_functions');
    }
};
