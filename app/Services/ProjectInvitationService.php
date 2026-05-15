<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectInvitationService
{
    public function __construct(private ProjectMemberService $memberService)
    {
    }

    /**
     * Create a project-level invitation.
     *
     * If the invitee is already a workspace member → add them directly to project_members
     * (no invitation token, no pending row). They get access immediately.
     *
     * If the invitee is external (not a workspace member) → create a pending invitation
     * and send the token email as normal.
     */
    public function createInvitation(
        Project $project,
        User $invitedBy,
        string $email,
        string $role = ProjectInvitation::ROLE_COMMENTER
    ): ProjectMember|ProjectInvitation {
        $email = strtolower(trim($email));

        // Check if this email belongs to an existing workspace member
        $existingUser = User::where('email', $email)->first();
        $isWorkspaceMember = $existingUser && $project->organization->hasMember($existingUser);

        if ($isWorkspaceMember) {
            // Auto-accept: add directly to project, no invitation row needed
            return DB::transaction(function () use ($project, $existingUser, $invitedBy, $role) {
                $projectMember = ProjectMember::where('project_id', $project->id)
                    ->where('user_id', $existingUser->id)
                    ->first();

                if ($projectMember) {
                    $projectMember->update([
                        'role'        => $role,
                        'access_type' => ProjectMember::ACCESS_TYPE_DIRECT_INVITE,
                        'invited_by'  => $invitedBy->id,
                    ]);
                } else {
                    $projectMember = ProjectMember::create([
                        'project_id'  => $project->id,
                        'user_id'     => $existingUser->id,
                        'role'        => $role,
                        'access_type' => ProjectMember::ACCESS_TYPE_DIRECT_INVITE,
                        'assigned_at' => now(),
                        'assigned_by' => $invitedBy->id,
                        'invited_by'  => $invitedBy->id,
                    ]);
                }

                return $projectMember->fresh();
            });
        }

        // External user — create pending invitation with token
        ProjectInvitation::where('project_id', $project->id)
            ->where('email', $email)
            ->pending()
            ->delete();

        $invitation = ProjectInvitation::create([
            'project_id'   => $project->id,
            'workspace_id' => $project->organization_id,
            'email'        => $email,
            'role'         => $role,
            'invited_by'   => $invitedBy->id,
            'token'        => Str::random(64),
            'status'       => 'pending',
            'expires_at'   => now()->addDays(7),
        ]);

        \App\Helpers\MailHelper::sendProjectInvitation($invitation);

        return $invitation;
    }

    /**
     * Validate and retrieve a project invitation by token.
     */
    public function validateToken(string $token): ProjectInvitation
    {
        $invitation = ProjectInvitation::where('token', $token)->first();

        if (!$invitation) {
            throw new \Exception('Invalid invitation token.');
        }

        if (!$invitation->isValid()) {
            throw new \Exception('This invitation has expired or has already been accepted.');
        }

        return $invitation;
    }

    /**
     * Accept a project invitation for an existing user.
     *
     * If the user is not yet a workspace member, they are added as a workspace member first.
     * The project role from the invitation is applied independently (overrides/supplements
     * any existing collaborator role).
     */
    public function accept(string $token, User $user): ProjectMember
    {
        $invitation = $this->validateToken($token);

        if ($invitation->email !== $user->email) {
            throw new \Exception('The invitation email does not match your account email.');
        }

        $project = $invitation->project;

        return DB::transaction(function () use ($invitation, $project, $user) {
            $projectMember = ProjectMember::where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->first();

            if ($projectMember) {
                // Already a project member — update role and mark as direct invite
                $projectMember->update([
                    'role'        => $invitation->role,
                    'access_type' => ProjectMember::ACCESS_TYPE_DIRECT_INVITE,
                    'invited_by'  => $invitation->invited_by,
                ]);
            } else {
                // New project member — no workspace membership required
                $projectMember = ProjectMember::create([
                    'project_id'  => $project->id,
                    'user_id'     => $user->id,
                    'role'        => $invitation->role,
                    'access_type' => ProjectMember::ACCESS_TYPE_DIRECT_INVITE,
                    'assigned_at' => now(),
                    'assigned_by' => $invitation->invited_by,
                    'invited_by'  => $invitation->invited_by,
                ]);
            }

            $invitation->markAccepted();

            return $projectMember->fresh();
        });
    }

    /**
     * Resend a project invitation.
     */
    public function resend(ProjectInvitation $invitation): ProjectInvitation
    {
        if (!$invitation->isPending()) {
            throw new \Exception('Cannot resend an invitation that has already been accepted.');
        }

        $invitation->update([
            'token'      => Str::random(64),
            'status'     => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        // Resend invitation email
        \App\Helpers\MailHelper::sendProjectInvitation($invitation);

        return $invitation;
    }

    /**
     * Cancel a project invitation.
     */
    public function cancel(ProjectInvitation $invitation): void
    {
        if (!$invitation->isPending()) {
            throw new \Exception('Cannot cancel an invitation that has already been accepted.');
        }

        $invitation->delete();
    }
}
