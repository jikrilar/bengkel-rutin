<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuzzy_rule_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuzzy_calculation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fuzzy_rule_id')->constrained()->restrictOnDelete();
            $table->decimal('alpha', 8, 4);
            $table->decimal('z_value', 8, 4);
            $table->decimal('weighted_value', 12, 4);
            $table->timestamps();

            $table->unique(['fuzzy_calculation_id', 'fuzzy_rule_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuzzy_rule_results');
    }
};
