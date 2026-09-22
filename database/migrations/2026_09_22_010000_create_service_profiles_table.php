<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->unsignedInteger('interval_km');
            $table->unsignedInteger('interval_days');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_profiles');
    }
};
