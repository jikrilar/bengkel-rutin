<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_profile_id')->constrained()->restrictOnDelete();
            $table->string('name', 100);
            $table->string('plate_number', 30);
            $table->string('plate_number_normalized', 30)->unique();
            $table->string('brand', 100);
            $table->string('model', 120);
            $table->unsignedSmallInteger('year');
            $table->date('baseline_service_date')->nullable();
            $table->unsignedInteger('baseline_odometer')->nullable();
            $table->string('baseline_source', 30)->nullable();
            $table->unsignedBigInteger('baseline_service_record_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
