<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case InService = 'in_service';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /** @return list<self> */
    public static function active(): array
    {
        return [self::Pending, self::Confirmed, self::InService];
    }

    /** @return list<string> */
    public static function activeValues(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::active());
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Konfirmasi',
            self::Confirmed => 'Dikonfirmasi',
            self::InService => 'Sedang Servis',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed, self::InService => 'brand',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }

    /** @return list<self> */
    public function transitions(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::InService, self::Cancelled],
            self::InService => [self::Completed],
            self::Completed, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->transitions(), true);
    }

    public function canBeRescheduled(): bool
    {
        return in_array($this, [self::Pending, self::Confirmed], true);
    }

    public function canBeCancelled(): bool
    {
        return in_array(self::Cancelled, $this->transitions(), true);
    }
}
