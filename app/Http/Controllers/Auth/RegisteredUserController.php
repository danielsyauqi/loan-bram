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
use Illuminate\Validation\Rule;

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
        // Trim username and email before validation
        $request->merge([
            'username' => trim($request->username),
            'email' => trim(strtolower($request->email)),
        ]);

        // Debug log for username check
        Log::info('Checking username', [
            'input' => $request->username,
            'exists' => User::where('username', $request->username)->exists(),
            'all_usernames' => User::pluck('username')->toArray(),
        ]);

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users'),
            ],
            'ic_number' => 'required|string|max:14',
            'phone' => 'required|string|max:15',
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users'),
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Create the user
        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'ic_num' => $validated['ic_number'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'status' => 'not verified',
            'email_verified_at' => now(),
            'role' => 'customer',
            'password' => Hash::make($validated['password']),
        ]);
        
        // Remove preverify_code cookie after registration
        setcookie('preverify_code', '', time() - 3600, '/');

        return redirect()->back()->with('success', 'Registration successful');
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
