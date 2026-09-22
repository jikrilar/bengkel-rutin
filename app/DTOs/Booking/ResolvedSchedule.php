<?php

namespace App\DTOs\Booking;

use Carbon\CarbonImmutable;

final readonly class ResolvedSchedule
{
    public function __construct(
        public CarbonImmutable $date,
        public bool $isOpen,
        public ?CarbonImmutable $opensAt,
        public ?CarbonImmutable $closesAt,
        public bool $exceptionApplied,
        public ?string $reason,
    ) {}
}
