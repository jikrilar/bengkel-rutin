<?php

namespace App\Livewire\Dashboard;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class Overview extends Component
{
    #[Url(as: 'vehicle')]
    public ?int $vehicleId = null;

    public function render(): View
    {
        $vehicles = auth()->user()->vehicles()
            ->with([
                'serviceProfile',
                'latestOdometer',
                'latestFuzzyCalculation',
                'activeBooking',
            ])
            ->latest()
            ->get();

        $selectedVehicle = $vehicles->firstWhere('id', $this->vehicleId) ?? $vehicles->first();

        if ($selectedVehicle !== null && $this->vehicleId !== $selectedVehicle->id) {
            $this->vehicleId = $selectedVehicle->id;
        }

        return view('livewire.dashboard.overview', compact('vehicles', 'selectedVehicle'));
    }
}
