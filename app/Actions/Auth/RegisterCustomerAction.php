<?php

namespace App\Actions\Auth;

use App\Enums\UserRole;
use App\Models\User;

class RegisterCustomerAction
{
    /**
     * @param  array{name: string, email: string, phone: string, password: string}  $attributes
     */
    public function execute(array $attributes): User
    {
        return User::query()->create([
            'name' => $attributes['name'],
            'email' => mb_strtolower($attributes['email']),
            'phone' => $attributes['phone'],
            'password' => $attributes['password'],
            'role' => UserRole::Customer,
        ]);
    }
}
