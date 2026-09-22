<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Vehicle\CreateVehicleAction;
use App\Actions\Vehicle\UpdateVehicleAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreVehicleRequest;
use App\Http\Requests\Customer\UpdateVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class VehicleController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Vehicle::class);

        $vehicles = request()->user()->vehicles()
            ->with(['latestOdometer', 'latestFuzzyCalculation'])
            ->latest()
            ->paginate(10);

        return view('customer.vehicles.index', compact('vehicles'));
    }

    public function create(): View
    {
        Gate::authorize('create', Vehicle::class);

        return view('customer.vehicles.create');
    }

    public function store(
        StoreVehicleRequest $request,
        CreateVehicleAction $action,
    ): RedirectResponse {
        $vehicle = $action->execute($request->user(), $request->validated());

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    public function show(Vehicle $vehicle): View
    {
        Gate::authorize('view', $vehicle);

        $vehicle->load([
            'serviceProfile',
            'latestOdometer',
            'latestFuzzyCalculation',
            'activeBooking',
            'serviceRecords' => fn ($query) => $query->latest('service_date')->limit(5),
        ]);
        $previousOdometer = null;
        $odometerRows = $vehicle->odometerLogs()
            ->orderBy('recorded_at')
            ->orderBy('id')
            ->get()
            ->map(function ($log) use (&$previousOdometer) {
                $log->setAttribute(
                    'display_delta',
                    $previousOdometer === null ? null : $log->odometer - $previousOdometer,
                );
                $previousOdometer = $log->odometer;

                return $log;
            })
            ->reverse()
            ->values();

        return view('customer.vehicles.show', compact('vehicle', 'odometerRows'));
    }

    public function edit(Vehicle $vehicle): View
    {
        Gate::authorize('update', $vehicle);

        return view('customer.vehicles.edit', compact('vehicle'));
    }

    public function update(
        UpdateVehicleRequest $request,
        Vehicle $vehicle,
        UpdateVehicleAction $action,
    ): RedirectResponse {
        $action->execute($request->user(), $vehicle, $request->validated());

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('success', 'Identitas kendaraan berhasil diperbarui.');
    }
}
