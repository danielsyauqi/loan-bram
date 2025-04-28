<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            if($request->user()->role === 'customer') {
                return redirect()->intended(route('customer.dashboard', absolute: false).'?verified=1');
            }else{
                return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
            }
        }   

        if ($request->user()->markEmailAsVerified()) {
            /** @var \Illuminate\Contracts\Auth\MustVerifyEmail $user */
            $user = $request->user();
            User::where('id', $user->id)->update(['status' => 'not verified']);
            event(new Verified($user));
            if($user->role === 'customer') {
                return redirect()->intended(route('customer.dashboard', absolute: false).'?verified=1');
            }else{
                return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
            }
        }
        
        $user = $request->user();
        if($user->role === 'customer') {
            return redirect()->intended(route('customer.dashboard', absolute: false).'?verified=1');
        }else{
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }
    }
}
