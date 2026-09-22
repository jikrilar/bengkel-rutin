<?php

namespace App\Enums;

enum BaselineSource: string
{
    case CustomerInput = 'customer_input';
    case ServiceRecord = 'service_record';
    case AdminCorrection = 'admin_correction';
}
