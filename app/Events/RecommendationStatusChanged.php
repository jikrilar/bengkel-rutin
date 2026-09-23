<?php

namespace App\Events;

use App\Enums\RecommendationStatus;
use App\Models\FuzzyCalculation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RecommendationStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly FuzzyCalculation $calculation,
        public readonly RecommendationStatus $previousStatus,
    ) {}
}
