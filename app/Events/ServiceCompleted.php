<?php

namespace App\Events;

use App\Models\ServiceRecord;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly ServiceRecord $serviceRecord) {}
}
