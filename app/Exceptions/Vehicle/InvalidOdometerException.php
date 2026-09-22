<?php

namespace App\Exceptions\Vehicle;

use DomainException;

class InvalidOdometerException extends DomainException
{
    // Domain marker for invalid chronological odometer input.
}
