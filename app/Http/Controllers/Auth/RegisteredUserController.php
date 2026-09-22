<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterCustomerAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterCustomerRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterCustomerRequest $request, RegisterCustomerAction $registerCustomer): RedirectResponse
    {
        $user = $registerCustomer->execute($request->validated());

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
