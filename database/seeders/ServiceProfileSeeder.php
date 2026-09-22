<?php

namespace Database\Seeders;

use App\Models\ServiceProfile;
use Illuminate\Database\Seeder;

class ServiceProfileSeeder extends Seeder
{
    public function run(): void
    {
        $profile = ServiceProfile::withTrashed()->firstOrNew([
            'name' => 'Servis Rutin Standar',
        ]);

        $profile->fill([
            'interval_km' => 4000,
            'interval_days' => 120,
            'description' => 'Profil servis rutin default untuk kendaraan customer.',
            'is_active' => true,
        ]);
        $profile->deleted_at = null;
        $profile->save();
    }
}
