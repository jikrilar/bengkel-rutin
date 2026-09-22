<?php

namespace App\Models;

use App\Enums\OdometerSource;
use Database\Factories\OdometerLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdometerLog extends Model
{
    /** @use HasFactory<OdometerLogFactory> */
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'odometer',
        'recorded_at',
        'source',
        'recorded_by',
        'correction_reason',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    protected function casts(): array
    {
        return [
            'odometer' => 'integer',
            'recorded_at' => 'datetime',
            'source' => OdometerSource::class,
        ];
    }
}
