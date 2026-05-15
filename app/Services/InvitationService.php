<?php

namespace App\Services;

use App\Models\OrganizationInvitation;
use App\Models\ProjectInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InvitationService
{
    public function __construct(
        private OrganizationService $organizationService,
        private ProjectMemberService $projectMemberService
    ) {
    }

    /**
     * Main entry point - processes invitation token.
     */
    public function process(string $token)
    {
        // Try to find invitation (organization or project)
        $result = $this->findInvitation($token);

        if (!$result) {
            abort(404, 'Invitation not found');
        }

        $invitation = $result['invitation'];
        $type = $result['type'];

        // Validate invitation
        $this->validateInvitation($invitation);

        // Check if this is a new user (before resolving)
        $isNewUser = !User::where('email', $invitation->email)->exists();

        // Resolve or create user
        $user = $this->resolveUser($invitation);

        // Attach to workspace/project
        $this->attachToWorkspaceOrProject($invitation, $type, $user);

        // Mark as accepted
        $this->markAccepted($invitation);

        // Redirect based on whether user is new
        if ($isNewUser) {
            return redirect()->route('password.set')
                ->with('status', 'Welcome! Please set your password to continue.');
        }

        // Redirect to appropriate location for existing users
        return $this->redirect($invitation, $type);
    }

    /**
     * Find invitation by token (organization or project).
     */
    private function findInvitation(string $token)
    {
        // Try organization invitation first
        $orgInvitation = OrganizationInvitation::where('token', $token)->first();
        if ($orgInvitation) {
            return ['invitation' => $orgInvitation, 'type' => 'organization'];
        }

        // Try project invitation
        $projectInvitation = ProjectInvitation::where('token', $token)->first();
        if ($projectInvitation) {
            return ['invitation' => $projectInvitation, 'type' => 'project'];
        }

        return null;
    }

    /**
     * Validate invitation (not expired, not already accepted).
     */
    private function validateInvitation($invitation): void
    {
        // Check if expired
        if ($invitation->expires_at && $invitation->expires_at < now()) {
            abort(410, 'This invitation has expired');
        }

        // Check if already accepted
        if ($invitation->accepted_at || (isset($invitation->status) && $invitation->status === 'accepted')) {
            // Redirect to login with info message
            redirect()->route('login')
                ->with('info', 'This invitation has already been accepted. Please log in.')
                ->send();
            exit;
        }
    }

    /**
     * Resolve user - find existing or create new.
     */
    private function resolveUser($invitation): User
    {
        $user = User::where('email', $invitation->email)->first();

        if ($user) {
            // User exists - just log them in
            Auth::login($user);
            return $user;
        }

        // User doesn't exist - create new account
        return $this->createUser($invitation);
    }

    /**
     * Create new user account.
     */
    private function createUser($invitation): User
    {
        $user = User::create([
            'email' => $invitation->email,
            'name' => $this->extractNameFromEmail($invitation->email),
            'password' => Hash::make(Str::random(32)), // Random temporary password
            'email_verified_at' => now(), // Auto-verify via invitation
            'must_set_password' => true, // Force password setup
        ]);

        // Log in the new user
        Auth::login($user);

        return $user;
    }

    /**
     * Extract name from email (before @).
     */
    private function extractNameFromEmail(string $email): string
    {
        $name = explode('@', $email)[0];
        return ucfirst(str_replace(['.', '_', '-'], ' ', $name));
    }

    /**
     * Attach user to workspace or project.
     */
    private function attachToWorkspaceOrProject($invitation, string $type, User $user): void
    {
        DB::transaction(function () use ($invitation, $type, $user) {
            if ($type === 'organization') {
                $this->attachToWorkspace($invitation, $user);
            } else {
                $this->attachToProject($invitation, $user);
            }
        });
    }

    /**
     * Attach user to workspace.
     */
    private function attachToWorkspace($invitation, User $user): void
    {
        $organization = $invitation->organization;

        // Check if already a member
        if ($organization->hasMember($user)) {
            return; // Already a member, skip
        }

        // Add member with proper role assignment
        $this->organizationService->addMember($organization, $user, $invitation->role);
    }

    /**
     * Attach user to project.
     * External users (not workspace members) are added as project-only members.
     * They are NOT added to the workspace.
     */
    private function attachToProject($invitation, User $user): void
    {
        $project = $invitation->project;

        $existingMember = $project->members()->where('user_id', $user->id)->first();

        if ($existingMember) {
            $existingMember->pivot->update([
                'role'        => $invitation->role,
                'access_type' => 'direct_invite',
                'invited_by'  => $invitation->invited_by,
            ]);
        } else {
            \App\Models\ProjectMember::create([
                'project_id'  => $project->id,
                'user_id'     => $user->id,
                'role'        => $invitation->role,
                'access_type' => 'direct_invite',
                'assigned_at' => now(),
                'assigned_by' => $invitation->invited_by,
                'invited_by'  => $invitation->invited_by,
            ]);
        }
    }

    /**
     * Mark invitation as accepted.
     */
    private function markAccepted($invitation): void
    {
        $invitation->update(['accepted_at' => now(), 'status' => 'accepted']);
    }

    /**
     * Redirect to appropriate location.
     */
    private function redirect($invitation, string $type)
    {
        if ($type === 'organization') {
            return redirect()->route('workspace.dashboard', $invitation->organization)
                ->with('status', 'Welcome! You have joined ' . $invitation->organization->name);
        }

        return redirect()->route('projects.show', $invitation->project)
            ->with('status', 'Welcome! You have joined ' . $invitation->project->name);
    }
}
