<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('function_impacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_function_id')->constrained()->onDelete('cascade');

            // CHANGED: Now points to the new metrics table, not categories
            $table->foreignId('quality_metric_id')->constrained()->onDelete('cascade');

            $table->integer('impact')->default(0);
            $table->timestamps();

            $table->unique(['city_function_id', 'quality_metric_id'], 'func_metric_impact_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('function_impacts');
    }
};
