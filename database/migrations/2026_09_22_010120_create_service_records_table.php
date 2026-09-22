<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_records', function (Blueprint $table) {
            $table->id();
            $table->string('service_code', 30)->unique();
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $table->foreignId('booking_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->dateTime('service_date')->index();
            $table->unsignedInteger('odometer');
            $table->string('service_type', 100);
            $table->text('complaint')->nullable();
            $table->text('work_performed');
            $table->text('notes')->nullable();
            $table->decimal('total_cost', 12, 2);
            $table->foreignId('completed_by')->constrained('users')->restrictOnDelete();
            $table->text('correction_reason')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'service_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_records');
    }
};
