<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuzzy_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $table->foreignId('fuzzy_config_id')->constrained()->restrictOnDelete();
            $table->string('trigger_type', 40)->index();

            $table->unsignedInteger('interval_km_snapshot');
            $table->unsignedInteger('interval_days_snapshot');
            $table->unsignedInteger('current_odometer');
            $table->unsignedInteger('baseline_odometer');
            $table->date('baseline_service_date');
            $table->unsignedInteger('km_since_service');
            $table->unsignedInteger('days_since_service');
            $table->decimal('average_daily_km', 10, 2)->nullable();
            $table->decimal('baseline_daily_usage', 10, 2);
            $table->decimal('progress_km', 8, 2);
            $table->decimal('progress_time', 8, 2);
            $table->decimal('usage_intensity', 8, 2)->nullable();

            $table->decimal('km_safe_mu', 8, 4);
            $table->decimal('km_approaching_mu', 8, 4);
            $table->decimal('km_critical_mu', 8, 4);
            $table->decimal('time_safe_mu', 8, 4);
            $table->decimal('time_approaching_mu', 8, 4);
            $table->decimal('time_critical_mu', 8, 4);
            $table->decimal('usage_normal_mu', 8, 4)->nullable();
            $table->decimal('usage_intensive_mu', 8, 4)->nullable();

            $table->decimal('score', 5, 2);
            $table->string('fuzzy_status', 30);
            $table->date('estimated_due_by_km')->nullable();
            $table->date('estimated_due_by_time');
            $table->date('estimated_due_date');
            $table->date('recommended_from_date')->nullable();
            $table->date('recommended_to_date')->nullable();
            $table->date('recommended_date');
            $table->string('final_status', 30)->index();
            $table->boolean('guard_applied')->default(false);
            $table->text('guard_reason')->nullable();
            $table->dateTime('calculated_at')->index();
            $table->timestamps();

            $table->index(['vehicle_id', 'calculated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuzzy_calculations');
    }
};
