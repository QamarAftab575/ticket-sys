<?php

namespace App\Services;

use App\Jobs\SendVerificationEmail;
use App\Models\User;

class EmailVerificationService
{
    /**
     * Send a verification email to the user.
     *
     * @param User $user
     * @return void
     */
    public function sendVerificationEmail(User $user): void
    {
        // Queue email job for async delivery
        SendVerificationEmail::dispatch($user);
    }

    /**
     * Mark the user's email as verified.
     *
     * @param User $user
     * @return void
     */
    public function verifyEmail(User $user): void
    {
        // Update email_verified_at timestamp
        $user->update(['email_verified_at' => now()]);
    }

    /**
     * Resend the verification email to the user.
     *
     * @param User $user
     * @return void
     */
    public function resendVerificationEmail(User $user): void
    {
        // Resend verification email
        $this->sendVerificationEmail($user);
    }
}
