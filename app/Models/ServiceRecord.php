<?php

namespace App\Models;

use Database\Factories\ServiceRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRecord extends Model
{
    /** @use HasFactory<ServiceRecordFactory> */
    use HasFactory;

    protected $fillable = [
        'service_code',
        'vehicle_id',
        'booking_id',
        'service_date',
        'odometer',
        'service_type',
        'complaint',
        'work_performed',
        'notes',
        'total_cost',
        'completed_by',
        'correction_reason',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    protected function casts(): array
    {
        return [
            'service_date' => 'datetime',
            'odometer' => 'integer',
            'total_cost' => 'decimal:2',
        ];
    }
}
