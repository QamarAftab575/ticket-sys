<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkspaceDashboardController extends Controller
{
    /**
     * Show the workspace dashboard.
     */
    public function show(Request $request, Organization $organization)
    {
        $user = auth()->user();

        // Check if user has access to this workspace
        if (!$organization->hasMember($user)) {
            abort(403, 'You do not have access to this workspace.');
        }

        // Get user's role in this workspace
        $userRole = $organization->getMemberRole($user);

        // Get all workspaces for the user (for switcher)
        $userWorkspaces = $user->organizations()
            ->where('organizations.is_active', true)
            ->whereNull('organization_memberships.deleted_at')
            ->select('organizations.id', 'organizations.name', 'organizations.avatar_color')
            ->get();

        // Get projects in this workspace
        $projects = $organization->projects()
            ->visibleTo($user)
            ->with('members')
            ->limit(10)
            ->get();

        // Get team members in this workspace
        $members = $organization->members()
            ->with('organizationMemberships')
            ->get();

        // Get pending invitations
        $invitations = $organization->invitations()
            ->whereNull('accepted_at')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'email', 'role', 'created_at', 'expires_at', 'accepted_at']);

        // Calculate setup progress
        $setupProgress = $this->calculateSetupProgress($organization, $user);

        return Inertia::render('Workspace/Dashboard', [
            'workspace' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'description' => $organization->description,
                'types' => $organization->types,
                'avatar_color' => $organization->avatar_color,
                'created_at' => $organization->created_at,
            ],
            'userRole' => $userRole,
            'userWorkspaces' => $userWorkspaces,
            'projects' => $projects,
            'members' => $members,
            'invitations' => $invitations,
            'setupProgress' => $setupProgress,
            'canEdit' => in_array($userRole, ['owner', 'admin']),
            'canInvite' => in_array($userRole, ['owner', 'admin']),
            'isOwner' => $userRole === 'owner',
        ]);
    }

    /**
     * Calculate workspace setup progress.
     */
    private function calculateSetupProgress(Organization $organization, $user): array
    {
        $steps = [
            [
                'id' => 'description',
                'title' => 'Add workspace description',
                'completed' => !empty($organization->description),
            ],
            [
                'id' => 'project',
                'title' => 'Create first project',
                'completed' => $organization->projects()->exists(),
            ],
            [
                'id' => 'teammates',
                'title' => 'Invite teammates',
                'completed' => $organization->members()->count() > 1,
            ],
        ];

        $completed = collect($steps)->filter(fn($step) => $step['completed'])->count();

        return [
            'steps' => $steps,
            'completed' => $completed,
            'total' => count($steps),
            'percentage' => (int) (($completed / count($steps)) * 100),
        ];
    }

    /**
     * Update workspace details.
     */
    public function updateWorkspace(Request $request, Organization $organization)
    {
        $user = auth()->user();

        if (!$organization->isAdmin($user)) {
            abort(403, 'You do not have permission to edit this workspace.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $organization->update($validated);

        return back()->with('status', 'Workspace updated successfully!');
    }

    /**
     * Switch to a different workspace.
     * Now only validates access - actual selection is handled in browser storage
     */
    public function switchWorkspace(Request $request, Organization $organization)
    {
        $user = auth()->user();

        if (!$organization->hasMember($user)) {
            abort(403, 'You do not have access to this workspace.');
        }

        return redirect()->route('workspace.dashboard', $organization);
    }

    /**
     * Delete the workspace and all its data.
     * Only the workspace owner can do this.
     * Requires the workspace name as confirmation.
     */
    public function destroy(Request $request, Organization $organization)
    {
        $user = auth()->user();

        if (!$organization->isOwner($user)) {
            abort(403, 'Only the workspace owner can delete this workspace.');
        }

        $request->validate([
            'workspace_name' => ['required', 'string', function ($attribute, $value, $fail) use ($organization) {
                if (strtolower(trim($value)) !== strtolower(trim($organization->name))) {
                    $fail('The workspace name does not match.');
                }
            }],
        ]);

        app(\App\Services\OrganizationService::class)->deleteOrganization($organization);

        return redirect()->route('dashboard')
            ->with('status', 'Workspace "' . $organization->name . '" has been permanently deleted.');
    }
}
