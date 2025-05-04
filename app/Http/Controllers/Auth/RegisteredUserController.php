<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Events\Registered;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request) : RedirectResponse
    {
        // CSRF 419 error is usually due to missing or invalid CSRF token.
        // Let's ensure the request is POST and has a valid session.
        // If this is an API request (e.g., from Vue with fetch/axios), make sure to send the XSRF-TOKEN cookie and X-XSRF-TOKEN header.

        // Log the request for debugging
        Log::info('Store request received', ['request' => $request->all()]);

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'ic_number' => 'required|string|max:14',
            'phone' => 'required|string|max:15',
            'username' => 'required|string|max:255|unique:users,username',
        ]);

        // Create the user
        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'ic_num' => $validated['ic_number'],
            'phone_num' => $validated['phone'],
            'role' => 'customer',
            'status' => 'not verified',
        ]);

        // Remove all cookies by expiring them
        foreach (array_keys($_COOKIE) as $name) {
            setcookie($name, '', time() - 3600, '/');
            unset($_COOKIE[$name]);
        }

        // Always redirect for Inertia requests
        return redirect()->route('registration.success');
    }

    public function registrationSuccess()
    {
        return Inertia::render('RegistrationSuccess');
    }

    public function NotActiveUser()
    {
        return Inertia::render('auth/NotActiveUser');
    }

}
