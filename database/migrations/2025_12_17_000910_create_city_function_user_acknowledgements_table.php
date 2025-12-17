<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('city_function_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_function_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Ensure a user only acknowledges a function once
            $table->unique(['city_function_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_function_user');
    }
};