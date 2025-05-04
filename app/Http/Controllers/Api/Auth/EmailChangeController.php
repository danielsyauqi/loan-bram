<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ConfirmNewEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailChangeController extends Controller
{
    // 1) User submits new address → save pending + send mail
    public function requestChange(Request $req)
    {
        $req->validate([
            'new_email' => 'required|email|unique:users,email|unique:users,pending_email',
        ]);
        $user = $req->user();
        $token = Str::random(40);
        $user->update([
            'pending_email'         => $req->new_email,
            'pending_email_token'   => $token,
            'pending_email_sent_at' => now(),
        ]);
        Mail::to($req->new_email)->send(new ConfirmNewEmail($user));
        return response()->json(['status' => 'pending', 'pending_email' => $req->new_email]);
    }

    // 2) Click link → confirm & swap
    public function confirm(Request $req)
    {
        $user = User::where('pending_email_token', $req->token)->first();
        $verified = false;
        $errorMessage = null;
        
        if ($user && !$user->pending_email_sent_at->lt(now()->subHours(24))) {
            $user->email = $user->pending_email;
            $user->pending_email = null;
            $user->pending_email_token = null;
            $user->pending_email_sent_at = null;
            $user->email_verified_at = now();
            $user->save();
            $verified = true;
        } else {
            // Set specific error message
            if (!$user) {
                $errorMessage = 'Invalid verification token. Please request a new verification email.';
            } else if ($user->pending_email_sent_at->lt(now()->subHours(24))) {
                $errorMessage = 'This verification link has expired. Please request a new verification email from your profile.';
            }
        }
        
        // Check if request expects JSON (API request)
        if ($req->expectsJson()) {
            return response()->json([
                'verified' => $verified,
                'message' => $verified ? 'Email verified successfully.' : ($errorMessage ?? 'Verification failed.'),
            ]);
        }
        
        // Check if request is from Inertia
        if ($req->header('X-Inertia')) {
            return inertia('EmailConfirmation', [
                'verified' => $verified,
                'errorMessage' => $errorMessage,
            ]);
        }
        
        // Default to Blade view
        return view('emails.confirmation-result', [
            'verified' => $verified,
            'errorMessage' => $errorMessage,
        ]);
    }

    // 3) Resend the pending link
    public function resend(Request $req)
    {
        $user = $req->user();
        if (! $user->pending_email) {
            return response()->json(['message' => 'No pending change'], 400);
        }
        $token = Str::random(40);
        $user->update([
            'pending_email_token'   => $token,
            'pending_email_sent_at' => now(),
        ]);
        Mail::to($user->pending_email)->send(new ConfirmNewEmail($user));
        return response()->json(['resent' => true]);
    }

    // 4) Cancel pending change
    public function cancel(Request $req)
    {
        $user = $req->user();
        $user->update([
            'pending_email'         => null,
            'pending_email_token'   => null,
            'pending_email_sent_at' => null,
        ]);
        return response()->json(['cancelled' => true]);
    }
}
