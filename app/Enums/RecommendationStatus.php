<?php

namespace App\Enums;

enum RecommendationStatus: string
{
    case NotNeeded = 'not_needed';
    case Approaching = 'approaching';
    case Urgent = 'urgent';
    case Unavailable = 'unavailable';
}
