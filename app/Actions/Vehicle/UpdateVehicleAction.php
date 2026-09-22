<?php

namespace App\Actions\Vehicle;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class UpdateVehicleAction
{
    /** @param array<string, mixed> $data */
    public function execute(User $user, Vehicle $vehicle, array $data): Vehicle
    {
        Gate::forUser($user)->authorize('update', $vehicle);

        return DB::transaction(function () use ($vehicle, $data): Vehicle {
            $normalizedPlate = Vehicle::normalizePlateNumber((string) $data['plate_number']);
            $duplicateExists = Vehicle::withTrashed()
                ->where('plate_number_normalized', $normalizedPlate)
                ->whereKeyNot($vehicle->id)
                ->exists();

            if ($duplicateExists) {
                throw ValidationException::withMessages([
                    'plate_number' => 'Nomor polisi ini sudah terdaftar.',
                ]);
            }

            $vehicle->update(Arr::only($data, [
                'name',
                'brand',
                'model',
                'year',
                'plate_number',
            ]));

            return $vehicle->fresh();
        }, attempts: 3);
    }
}
