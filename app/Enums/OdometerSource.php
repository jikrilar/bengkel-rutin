<?php

namespace App\Enums;

enum OdometerSource: string
{
    case Initial = 'initial';
    case CustomerUpdate = 'customer_update';
    case Service = 'service';
    case AdminCorrection = 'admin_correction';

    public function label(): string
    {
        return match ($this) {
            self::Initial => 'Data awal',
            self::CustomerUpdate => 'Update customer',
            self::Service => 'Catatan servis',
            self::AdminCorrection => 'Koreksi admin',
        };
    }
}
