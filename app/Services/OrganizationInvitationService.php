<?php

namespace App\Services;

use App\Helpers\MailHelper;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\OrganizationMembership;
use App\Models\User;
use Illuminate\Support\Str;

class OrganizationInvitationService
{
    /**
     * Create an invitation for an email address.
     */
    public function createInvitation(
        Organization $organization,
        User $invitedBy,
        string $email,
        string $role = 'member'
    ): OrganizationInvitation {
        // Check if invitation already exists for this email in this org
        $existing = OrganizationInvitation::where('organization_id', $organization->id)
            ->where('email', $email)
            ->pending()
            ->first();

        if ($existing && !$existing->isExpired()) {
            throw new \Exception('An active invitation already exists for this email.');
        }

        // Generate a unique token
        $token = Str::random(64);

        $invitation = OrganizationInvitation::create([
            'organization_id' => $organization->id,
            'email'           => $email,
            'role'            => $role,
            'invited_by'      => $invitedBy->id,
            'token'           => $token,
            'expires_at'      => now()->addDays(7),
        ]);

        MailHelper::sendInvitation($invitation);

        return $invitation;
    }

    /**
     * Validate and retrieve an invitation by token.
     */
    public function validateToken(string $token): OrganizationInvitation
    {
        $invitation = OrganizationInvitation::where('token', $token)->first();

        if (!$invitation) {
            throw new \Exception('Invalid invitation token.');
        }

        if (!$invitation->isValid()) {
            throw new \Exception('This invitation has expired or has already been accepted.');
        }

        return $invitation;
    }

    /**
     * Accept invitation for an existing user.
     */
    public function acceptForExistingUser(string $token, User $user): OrganizationMembership
    {
        $invitation = $this->validateToken($token);

        // Verify the email matches
        if ($invitation->email !== $user->email) {
            throw new \Exception('The invitation email does not match your account email.');
        }

        // Use OrganizationService to properly add member with Spatie roles
        $organizationService = app(OrganizationService::class);
        $membership = $organizationService->addMember($invitation->organization, $user, $invitation->role);

        // Mark invitation as accepted
        $invitation->update(['accepted_at' => now()]);

        return $membership;
    }

    /**
     * Accept invitation for a new user (after registration).
     */
    public function acceptForNewUser(string $token, User $user): OrganizationMembership
    {
        $invitation = $this->validateToken($token);

        // Verify the email matches
        if ($invitation->email !== $user->email) {
            throw new \Exception('The invitation email does not match your registration email.');
        }

        // Use OrganizationService to properly add member with Spatie roles
        $organizationService = app(OrganizationService::class);
        $membership = $organizationService->addMember($invitation->organization, $user, $invitation->role);

        // Mark invitation as accepted
        $invitation->update(['accepted_at' => now()]);

        return $membership;
    }

    /**
     * Resend an invitation.
     */
    public function resendInvitation(OrganizationInvitation $invitation): OrganizationInvitation
    {
        if (!$invitation->isPending()) {
            throw new \Exception('Cannot resend an invitation that has already been accepted.');
        }

        $invitation->update([
            'token'      => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        MailHelper::sendInvitation($invitation);

        return $invitation;
    }

    /**
     * Cancel an invitation.
     */
    public function cancelInvitation(OrganizationInvitation $invitation): void
    {
        if (!$invitation->isPending()) {
            throw new \Exception('Cannot cancel an invitation that has already been accepted.');
        }

        $invitation->delete();
    }
}
