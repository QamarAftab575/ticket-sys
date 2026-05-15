<?php

namespace App\Helpers;

use App\Mail\InvitationMail;
use App\Mail\ProjectInvitationMail;
use App\Models\OrganizationInvitation;
use App\Models\ProjectInvitation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailHelper
{
    /**
     * Send a workspace invitation email.
     * Silently logs on failure so it never breaks the invitation flow.
     */
    public static function sendInvitation(OrganizationInvitation $invitation): void
    {
        try {
            Mail::to($invitation->email)->queue(new InvitationMail($invitation));
        } catch (\Throwable $e) {
            Log::error('Failed to send invitation email', [
                'email'           => $invitation->email,
                'organization_id' => $invitation->organization_id,
                'error'           => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send a project invitation email.
     * Silently logs on failure so it never breaks the invitation flow.
     */
    public static function sendProjectInvitation(ProjectInvitation $invitation): void
    {
        try {
            Mail::to($invitation->email)->queue(new ProjectInvitationMail($invitation));
        } catch (\Throwable $e) {
            Log::error('Failed to send project invitation email', [
                'email'      => $invitation->email,
                'project_id' => $invitation->project_id,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
