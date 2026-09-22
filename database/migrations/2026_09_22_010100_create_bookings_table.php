<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 30)->unique();
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $table->foreignId('recommendation_calculation_id')->nullable()->constrained('fuzzy_calculations')->nullOnDelete();
            $table->dateTime('scheduled_at')->index();
            $table->unsignedSmallInteger('duration_minutes');
            $table->string('status', 30)->index();
            $table->text('complaint')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index(['scheduled_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
