<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class RecommendationController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Vehicle::class);

        $vehicles = request()->user()->vehicles()
            ->with(['latestOdometer', 'latestFuzzyCalculation'])
            ->latest()
            ->get();

        return view('customer.recommendations.index', compact('vehicles'));
    }

    public function show(Vehicle $vehicle): View
    {
        Gate::authorize('view', $vehicle);

        $vehicle->load(['serviceProfile', 'latestOdometer', 'latestFuzzyCalculation']);

        return view('customer.recommendations.show', [
            'vehicle' => $vehicle,
            'calculation' => $vehicle->latestFuzzyCalculation,
        ]);
    }
}
