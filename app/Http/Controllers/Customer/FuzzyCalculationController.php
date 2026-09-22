<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\FuzzyCalculation;
use App\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class FuzzyCalculationController extends Controller
{
    public function show(Vehicle $vehicle, FuzzyCalculation $calculation): View
    {
        Gate::authorize('view', $vehicle);
        abort_unless($calculation->vehicle_id === $vehicle->id, 404);
        Gate::authorize('view', $calculation);

        $calculation->load([
            'vehicle',
            'fuzzyConfig',
            'ruleResults' => fn ($query) => $query->with('rule')->orderBy('fuzzy_rule_id'),
        ]);

        return view('customer.recommendations.calculation', compact('vehicle', 'calculation'));
    }
}
