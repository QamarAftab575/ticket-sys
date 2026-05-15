<?php

namespace App\Jobs;

use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendInvitationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Invitation $invitation,
        public User $user
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Generate acceptance URL with token
        $acceptanceUrl = route('accept-invitation.show', ['token' => $this->invitation->token]);

        // Send InvitationMail
        Mail::send(new InvitationMail($this->user, $acceptanceUrl, $this->invitation->expires_at));

        // Log email sent event
        \Log::info('Invitation email sent', [
            'invitation_id' => $this->invitation->id,
            'user_id' => $this->user->id,
            'email' => $this->user->email,
        ]);
    }
}
