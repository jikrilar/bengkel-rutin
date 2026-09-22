<?php

namespace App\Actions\Service;

use App\Enums\BaselineSource;
use App\Enums\BookingStatus;
use App\Enums\CalculationTrigger;
use App\Enums\OdometerSource;
use App\Enums\UserRole;
use App\Events\ServiceCompleted;
use App\Models\Booking;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\Booking\BookingTransitionService;
use App\Services\Recommendation\RecommendationService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CompleteServiceAction
{
    public function __construct(
        private readonly BookingTransitionService $transitionService,
        private readonly RecommendationService $recommendationService,
    ) {}

    /**
     * @param array{
     *   service_date: string,
     *   odometer: int,
     *   service_type: string,
     *   complaint?: string|null,
     *   work_performed: string,
     *   notes?: string|null,
     *   total_cost: int|float|string
     * } $data
     */
    public function execute(User $admin, Booking $booking, array $data): ServiceRecord
    {
        if ($admin->role !== UserRole::Admin) {
            throw new AuthorizationException('Hanya admin yang dapat menyelesaikan servis.');
        }

        Gate::forUser($admin)->authorize('update', $booking);
        $validated = Validator::make($data, [
            'service_date' => ['required', 'date', 'before_or_equal:now'],
            'odometer' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'service_type' => ['required', 'string', 'max:100'],
            'complaint' => ['nullable', 'string', 'max:5000'],
            'work_performed' => ['required', 'string', 'max:10000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'total_cost' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
        ])->validate();

        $serviceRecord = DB::transaction(function () use ($admin, $booking, $validated): ServiceRecord {
            $lockedBooking = Booking::query()->with('vehicle')->lockForUpdate()->findOrFail($booking->id);
            Gate::forUser($admin)->authorize('update', $lockedBooking);

            if ($lockedBooking->status !== BookingStatus::InService) {
                throw ValidationException::withMessages([
                    'booking' => 'Servis hanya dapat diselesaikan dari status Sedang Servis.',
                ]);
            }

            $vehicle = Vehicle::query()->lockForUpdate()->findOrFail($lockedBooking->vehicle_id);
            $latestOdometer = $vehicle->odometerLogs()
                ->orderByDesc('recorded_at')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();
            $serviceDate = CarbonImmutable::parse($validated['service_date'], config('app.timezone'));

            if ($latestOdometer === null) {
                throw ValidationException::withMessages([
                    'odometer' => 'Kendaraan belum memiliki catatan odometer awal.',
                ]);
            }

            if ((int) $validated['odometer'] < $latestOdometer->odometer) {
                throw ValidationException::withMessages([
                    'odometer' => sprintf(
                        'Odometer servis minimal %s km.',
                        number_format($latestOdometer->odometer, 0, ',', '.'),
                    ),
                ]);
            }

            if ($serviceDate->lt($latestOdometer->recorded_at)) {
                throw ValidationException::withMessages([
                    'service_date' => 'Tanggal servis tidak boleh lebih awal dari catatan odometer terakhir.',
                ]);
            }

            $serviceRecord = ServiceRecord::query()->create([
                'service_code' => sprintf('SRV-%s-%04d', $serviceDate->format('Y'), $lockedBooking->id),
                'vehicle_id' => $vehicle->id,
                'booking_id' => $lockedBooking->id,
                'service_date' => $serviceDate,
                'odometer' => $validated['odometer'],
                'service_type' => trim($validated['service_type']),
                'complaint' => filled($validated['complaint'] ?? null) ? trim($validated['complaint']) : null,
                'work_performed' => trim($validated['work_performed']),
                'notes' => filled($validated['notes'] ?? null) ? trim($validated['notes']) : null,
                'total_cost' => $validated['total_cost'],
                'completed_by' => $admin->id,
            ]);

            $vehicle->odometerLogs()->create([
                'odometer' => $validated['odometer'],
                'recorded_at' => $serviceDate,
                'source' => OdometerSource::Service,
                'recorded_by' => $admin->id,
            ]);
            $vehicle->update([
                'baseline_service_date' => $serviceDate->toDateString(),
                'baseline_odometer' => $validated['odometer'],
                'baseline_source' => BaselineSource::ServiceRecord,
                'baseline_service_record_id' => $serviceRecord->id,
            ]);

            $this->transitionService->transition(
                $lockedBooking,
                BookingStatus::Completed,
                $admin,
                'Servis selesai dan catatan servis dibuat.',
            );

            $vehicle->unsetRelation('latestOdometer');
            $vehicle->unsetRelation('serviceProfile');
            $this->recommendationService->calculateAndPersist(
                $vehicle,
                CalculationTrigger::ServiceCompleted,
                $serviceDate,
            );

            return $serviceRecord->load(['vehicle.user', 'booking', 'completedBy']);
        }, attempts: 3);

        ServiceCompleted::dispatch($serviceRecord);

        return $serviceRecord;
    }
}
