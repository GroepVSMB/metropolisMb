<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use App\Models\CityFunction;
use App\Observers\CityFunctionObserver;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        CityFunction::observe(CityFunctionObserver::class);

        // AUTO-MIGRATION FIX: Ensure 'condition' column exists
        if (Schema::hasTable('function_impacts') && !Schema::hasColumn('function_impacts', 'condition')) {
            try {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE function_impacts ADD COLUMN `condition` VARCHAR(50) DEFAULT 'always' AFTER impact");
            } catch (\Exception $e) {
                // Log error or ignore if already exists/race condition
                \Illuminate\Support\Facades\Log::error('Auto-migration failed: ' . $e->getMessage());
            }
        }

        // AUTO-MIGRATION FIX: Drop Unique Constraint (to allow Day/Night splits)
        try {
            if (Schema::hasTable('function_impacts')) {
                Schema::table('function_impacts', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->dropUnique('func_metric_impact_unique');
                });
            }
        } catch (\Exception $e) {
            // Ignore if index doesn't exist
        }
    }
}


