<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (! $email || ! $password) {
            $this->command?->warn('Admin seed dilewati: ADMIN_EMAIL dan ADMIN_PASSWORD belum diatur.');

            return;
        }

        User::query()->updateOrCreate(
            ['email' => mb_strtolower($email)],
            [
                'name' => config('admin.name'),
                'phone' => config('admin.phone'),
                'password' => $password,
                'role' => UserRole::Admin,
            ],
        );
    }
}
