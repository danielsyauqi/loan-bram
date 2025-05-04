<?php

namespace App\Http\Controllers\Auth;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\TempUser;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\PreVerificationEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;

class EmailPreVerificationController extends Controller
{
    /**
     * Show the email verification form for the first step of registration.
     */
    public function create(): Response
    {
        return Inertia::render('auth/VerifyEmailFirst');
    }

    /**
     * Send verification email to the provided email address.
     */
    public function sendVerification(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email',
        ]);

        // Check if there is a previous temp record for this email and delete it
        TempUser::where('email', $request->email)->delete();

        // Create a temp record with token
        $code = random_int(100000, 999999);
        $tempUser = TempUser::create([
            'email' => $request->email,
            'code' => $code,
            'expires_at' => now()->addHours(1),
        ]);

        // Generate verification code
        $verificationCode = $code;

        // Send verification email
        Mail::to($request->email)->send(new PreVerificationEmail($verificationCode));

        return back()->with('status', 'verification-code-sent');
    }
    
    public function invalidVerificationLink(): Response
    {
        return Inertia::render('auth/InvalidVerificationLink');
    }


    /**
     * Cancel the email verification process.
     */
    public function cancel(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Delete the temp record for this email
        TempUser::where('email', $request->email)->delete();
        // Clear the session
        // Remove all cookies by expiring them
        foreach (array_keys($_COOKIE) as $name) {
            setcookie($name, '', time() - 3600, '/');
            unset($_COOKIE[$name]);
        }

        return response()->json(['message' => 'Verification cancelled.']);
    }

    /**
     * Get the current verified email from session.
     */
    public function currentVerifiedEmail(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json(['verified_email' => $request->session()->get('verified_email')]);
    }

    /**
     * Get the email by preverify_token cookie.
     */
    public function getEmailByCode(Request $request): \Illuminate\Http\JsonResponse
    {
        $code = $request->cookie('preverify_code'); // retrieve cookie value
        Log::info('Code to get:', ['code' => $code]);

        if (!$code) {
            return response()->json(['email' => null], 404);
        }

        $tempUser = TempUser::where('code', $code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$tempUser) {
            return response()->json(['email' => null], 404);
        }

        Log::info('Email found:', ['email' => $tempUser->email]);

        return response()->json(['email' => $tempUser->email]);
    }

    /**
     * Verify the email by code (API, no signature required)
     */
    public function verifyByCode(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric'
        ]);

        $tempUser = TempUser::where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$tempUser) {
            return response()->json(['message' => 'Your verification code is incorrect or has expired. Please check your email and try again.'], 422);
        }

        session(['verified_email' => $tempUser->email]);

        $cookie = cookie(
            'preverify_code',
            $tempUser->code,
            60,
            '/',
            null,
            config('session.secure_cookie', false),
            false,
            false,
            'lax'
        );

        return response()->json(['message' => 'Email verified.'])->withCookie($cookie);
    }
} 