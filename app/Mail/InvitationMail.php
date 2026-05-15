<?php

namespace App\Mail;

use App\Models\OrganizationInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public OrganizationInvitation $invitation)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'ve been invited to join ' . $this->invitation->organization->name,
        );
    }

    public function content(): Content
    {
        $acceptUrl = route('invitation.accept', ['token' => $this->invitation->token]);

        return new Content(
            markdown: 'emails.invitation',
            with: [
                'organizationName' => $this->invitation->organization->name,
                'invitedByName'    => $this->invitation->invitedBy->name ?? 'A team member',
                'acceptUrl'        => $acceptUrl,
                'expiresAt'        => $this->invitation->expires_at,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
