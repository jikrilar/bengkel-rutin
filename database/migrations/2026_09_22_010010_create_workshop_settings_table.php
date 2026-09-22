<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshop_settings', function (Blueprint $table) {
            $table->id();
            $table->string('workshop_name', 150);
            $table->text('address');
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->string('timezone', 50)->default('Asia/Jakarta');
            $table->unsignedSmallInteger('slot_duration_minutes')->default(60);
            $table->unsignedSmallInteger('slot_capacity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshop_settings');
    }
};
