<?php

namespace App\DTOs\Booking;

use Carbon\CarbonImmutable;

final readonly class BookingSlot
{
    public function __construct(
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
        public int $capacity,
        public int $remainingCapacity,
        public bool $isAvailable,
        public string $state,
        public ?string $reason = null,
    ) {}

    public function label(): string
    {
        return $this->startsAt->format('H.i');
    }

    public function stateLabel(): string
    {
        return match ($this->state) {
            'available' => 'Tersedia',
            'limited' => 'Terbatas',
            'full' => 'Penuh',
            default => 'Tutup',
        };
    }
}
