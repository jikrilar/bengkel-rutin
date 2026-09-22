<?php

namespace App\Enums;

enum OdometerSource: string
{
    case Initial = 'initial';
    case CustomerUpdate = 'customer_update';
    case Service = 'service';
    case AdminCorrection = 'admin_correction';
}
