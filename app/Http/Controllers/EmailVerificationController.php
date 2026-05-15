<?php

namespace App\Http\Controllers;

use App\Services\EmailVerificationService;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailVerificationController extends Controller
{
    public function __construct(private EmailVerificationService $emailVerificationService)
    {
    }

    // Show verification page
    public function show()
    {
        return Inertia::render('Auth/VerifyEmail');
    }

    // Verify email via signed URL
    public function verify(EmailVerificationRequest $request)
    {
        $this->emailVerificationService->verifyEmail($request->user());

        return redirect()->route('dashboard')->with('status', 'Email verified successfully!');
    }

    // Resend verification email
    public function resend(Request $request)
    {
        $this->emailVerificationService->resendVerificationEmail($request->user());

        return back()->with('status', 'Verification link sent!');
    }
}
