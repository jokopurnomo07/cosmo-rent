<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Determine redirection path based on role
        $redirect = $user->hasRole('user')
            ? redirect()->route('user.dashboard')
            : redirect()->route('admin.dashboard');

        // If already verified, return redirect
        if ($user->hasVerifiedEmail()) {
            return $redirect;
        }

        // Mark email as verified and trigger event
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $redirect;
    }

}
