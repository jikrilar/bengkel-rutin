<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuzzy_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('version')->unique();
            $table->decimal('progress_safe_end', 6, 2)->default(70);
            $table->decimal('progress_approaching_peak', 6, 2)->default(90);
            $table->decimal('progress_critical_full', 6, 2)->default(100);
            $table->decimal('usage_normal_full_until', 6, 2)->default(80);
            $table->decimal('usage_intensive_full_from', 6, 2)->default(120);
            $table->boolean('is_active')->default(false)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuzzy_configs');
    }
};
