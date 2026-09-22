<?php

namespace App\Livewire\Bookings;

use App\Actions\Booking\CreateBookingAction;
use App\Exceptions\Booking\BookingSlotUnavailableException;
use App\Services\Booking\BookingAvailabilityService;
use App\Services\Workshop\WorkshopScheduleService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

class CreateBooking extends Component
{
    #[Url(as: 'vehicle')]
    public ?int $vehicleId = null;

    #[Url]
    public string $date = '';

    public string $time = '';

    public string $complaint = '';

    public function mount(): void
    {
        $vehicleIds = auth()->user()->vehicles()->pluck('id');

        if ($this->vehicleId === null && $vehicleIds->count() === 1) {
            $this->vehicleId = $vehicleIds->first();
        }

        if ($this->vehicleId !== null && ! $vehicleIds->contains($this->vehicleId)) {
            abort(403);
        }

        $this->date = $this->date !== '' ? $this->date : now()->addDay()->toDateString();
    }

    public function updatedVehicleId(): void
    {
        $this->time = '';
    }

    public function updatedDate(): void
    {
        $this->time = '';
    }

    public function submit(CreateBookingAction $action): void
    {
        $vehicleIds = auth()->user()->vehicles()->pluck('id')->all();
        $data = $this->validate([
            'vehicleId' => ['required', 'integer', Rule::in($vehicleIds)],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'complaint' => ['nullable', 'string', 'max:2000'],
        ], [
            'vehicleId.required' => 'Pilih kendaraan yang akan diservis.',
            'date.after_or_equal' => 'Pilih tanggal hari ini atau setelahnya.',
            'time.required' => 'Pilih salah satu slot yang tersedia.',
        ]);

        $vehicle = auth()->user()->vehicles()
            ->with('latestFuzzyCalculation')
            ->findOrFail($data['vehicleId']);
        Gate::authorize('view', $vehicle);

        try {
            $booking = $action->execute(
                auth()->user(),
                $vehicle,
                $data['date'].' '.$data['time'],
                $data['complaint'] ?: null,
                $vehicle->latestFuzzyCalculation?->id,
            );
        } catch (BookingSlotUnavailableException $exception) {
            $this->addError('time', $exception->reason);
            $this->time = '';

            return;
        }

        session()->flash('success', 'Booking berhasil dibuat dan menunggu konfirmasi bengkel.');
        $this->redirectRoute('bookings.show', $booking, navigate: true);
    }

    public function render(
        BookingAvailabilityService $availability,
        WorkshopScheduleService $scheduleService,
    ): View {
        $vehicles = auth()->user()->vehicles()
            ->with('latestFuzzyCalculation')
            ->orderBy('name')
            ->get();
        $vehicle = $this->vehicleId
            ? $vehicles->firstWhere('id', $this->vehicleId)
            : null;
        $settings = $scheduleService->settings();
        $selectedDate = rescue(
            fn () => CarbonImmutable::parse($this->date, $settings->timezone),
            report: false,
        );
        $slots = $selectedDate ? $availability->slotsForDate($selectedDate, settings: $settings) : collect();
        $resolvedSchedule = $selectedDate ? $scheduleService->resolve($selectedDate, $settings) : null;
        $calculation = $vehicle?->latestFuzzyCalculation;
        $outsideRecommendedWindow = $calculation && $selectedDate
            ? $selectedDate->startOfDay()->lt($calculation->recommended_from_date->startOfDay())
                || ($calculation->recommended_to_date
                    && $selectedDate->startOfDay()->gt($calculation->recommended_to_date->startOfDay()))
            : false;

        return view('livewire.bookings.create-booking', compact(
            'vehicles',
            'vehicle',
            'settings',
            'selectedDate',
            'slots',
            'resolvedSchedule',
            'calculation',
            'outsideRecommendedWindow',
        ));
    }
}
