<?php

namespace App\Enums;

enum CalculationTrigger: string
{
    case VehicleCreated = 'vehicle_created';
    case OdometerUpdated = 'odometer_updated';
    case DailyScheduler = 'daily_scheduler';
    case ServiceCompleted = 'service_completed';
    case ServiceProfileChanged = 'service_profile_changed';
    case FuzzyConfigChanged = 'fuzzy_config_changed';
    case ManualRecalculate = 'manual_recalculate';
}
