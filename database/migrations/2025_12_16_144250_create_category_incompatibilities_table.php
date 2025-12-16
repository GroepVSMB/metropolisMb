<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('category_incompatibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            // Note: You might need to specify the table name for the second foreign key explicitly if it fails too
            $table->foreignId('incompatible_category_id')->constrained('categories')->onDelete('cascade');
            $table->timestamps();

            // FIX: Add a second argument 'cat_incompat_unique' to shorten the name
            $table->unique(['category_id', 'incompatible_category_id'], 'cat_incompat_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_incompatibilities');
    }
};
