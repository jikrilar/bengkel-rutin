<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Vehicle\UpdateOdometerAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreOdometerRequest;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;

class OdometerController extends Controller
{
    public function store(
        StoreOdometerRequest $request,
        Vehicle $vehicle,
        UpdateOdometerAction $action,
    ): RedirectResponse {
        $action->execute($request->user(), $vehicle, $request->validated());

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('success', 'Odometer berhasil diperbarui. Rekomendasi terbaru sudah dihitung.');
    }
}
