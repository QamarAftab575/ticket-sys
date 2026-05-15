<?php

namespace App\Jobs;

use App\Mail\ResetPasswordEmail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPasswordResetEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public string $token
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Generate reset URL with token
        $resetUrl = route('password.reset', ['token' => $this->token, 'email' => $this->user->email]);

        // Send ResetPasswordEmail
        Mail::send(new ResetPasswordEmail($this->user, $resetUrl));

        // Log email sent event
        \Log::info('Password reset email sent', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
        ]);
    }
}
