<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Category;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('city_functions', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // Correct way: Just put the line here. REMOVE ->after('name')
            $table->string('image')->nullable();

            $table->foreignIdFor(Category::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_functions');
    }
};
