<?php

namespace App\Enums;

enum RecommendationStatus: string
{
    case NotNeeded = 'not_needed';
    case Approaching = 'approaching';
    case Urgent = 'urgent';
    case Unavailable = 'unavailable';

    public static function fromScore(float $score): self
    {
        if (! is_finite($score) || $score < 0 || $score > 100) {
            throw new \InvalidArgumentException('Recommendation score must be between 0 and 100.');
        }

        return match (true) {
            $score < 40 => self::NotNeeded,
            $score < 70 => self::Approaching,
            default => self::Urgent,
        };
    }

    public function urgencyRank(): int
    {
        return match ($this) {
            self::Unavailable => -1,
            self::NotNeeded => 0,
            self::Approaching => 1,
            self::Urgent => 2,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::NotNeeded => 'Belum Perlu Servis',
            self::Approaching => 'Servis Mendekat',
            self::Urgent => 'Segera Servis',
            self::Unavailable => 'Rekomendasi Belum Tersedia',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::NotNeeded => 'success',
            self::Approaching => 'warning',
            self::Urgent => 'danger',
            self::Unavailable => 'neutral',
        };
    }

    public function explanation(): string
    {
        return match ($this) {
            self::NotNeeded => 'Kendaraan masih berada dalam rentang servis yang aman.',
            self::Approaching => 'Jadwal servis mulai mendekat. Siapkan waktu kunjungan yang sesuai.',
            self::Urgent => 'Kebutuhan servis sudah mendesak. Jadwalkan kunjungan sesegera mungkin.',
            self::Unavailable => 'Lengkapi baseline servis untuk mendapatkan rekomendasi.',
        };
    }
}
