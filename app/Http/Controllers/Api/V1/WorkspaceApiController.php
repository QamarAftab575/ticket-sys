<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\BillingHelper;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Services\OrganizationInvitationService;
use App\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkspaceApiController extends Controller
{
    protected OrganizationService $organizationService;
    protected OrganizationInvitationService $invitationService;

    public function __construct(
        OrganizationService $organizationService,
        OrganizationInvitationService $invitationService
    ) {
        $this->organizationService = $organizationService;
        $this->invitationService = $invitationService;
    }

    /**
     * Get workspaces
     * 
     * Returns all workspaces the authenticated user belongs to.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $workspaces = $this->organizationService->getOrganizationsForUser($user);

        $data = $workspaces->map(function ($workspace) use ($user) {
            return [
                'id' => $workspace->id,
                'name' => $workspace->name,
                'description' => $workspace->description,
                'types' => $workspace->types,
                'avatar_color' => $workspace->avatar_color,
                'is_active' => $workspace->is_active,
                'role' => $user->getWorkspaceRole($workspace->id),
                'is_owner' => $user->isWorkspaceOwner($workspace->id),
                'is_admin' => $user->isWorkspaceAdmin($workspace->id),
                'created_at' => $workspace->created_at->toIso8601String(),
                'updated_at' => $workspace->updated_at->toIso8601String(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Get workspace members
     */
    public function members(Organization $organization): JsonResponse
    {
        $this->authorize('view', $organization);

        $perPage = request()->input('per_page', 50); // Default 50 records per page
        $perPage = min($perPage, 100); // Max 100 per page

        $members = $organization->memberships()
            ->with('user:id,name,email,avatar')
            ->paginate($perPage);

        $data = $members->getCollection()->map(function ($membership) {
            return [
                'id' => $membership->user->id,
                'name' => $membership->user->name,
                'email' => $membership->user->email,
                'avatar' => $membership->user->avatar,
                'role' => $membership->role,
                'joined_at' => $membership->joined_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => $members->currentPage(),
                'last_page' => $members->lastPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
                'from' => $members->firstItem(),
                'to' => $members->lastItem(),
            ],
        ]);
    }

    /**
     * Get workspace users (alias for members)
     */
    public function users(Organization $organization): JsonResponse
    {
        return $this->members($organization);
    }

    /**
     * Invite a member to workspace
     */
    public function inviteMember(Request $request, Organization $organization): JsonResponse
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', 'in:owner,member'],
        ]);

        // Check if user can add a member (quota check)
        if (!BillingHelper::canAddMember($organization)) {
            return response()->json([
                'message' => 'Your member quota has been reached. Please upgrade your plan to add more members.',
                'error' => 'MEMBER_QUOTA_EXCEEDED',
            ], 403);
        }

        try {
            $invitation = $this->invitationService->createInvitation(
                $organization,
                Auth::user(),
                $validated['email'],
                $validated['role']
            );

            return response()->json([
                'message' => 'Invitation sent successfully',
                'data' => [
                    'id' => $invitation->id,
                    'email' => $invitation->email,
                    'role' => $invitation->role,
                    'expires_at' => $invitation->expires_at->toIso8601String(),
                    'created_at' => $invitation->created_at->toIso8601String(),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get pending invitations for workspace
     */
    public function pendingInvitations(Organization $organization): JsonResponse
    {
        $this->authorize('view', $organization);

        $perPage = request()->input('per_page', 50);
        $perPage = min($perPage, 100);

        $invitations = $organization->invitations()
            ->pending()
            ->with('inviter:id,name,email')
            ->paginate($perPage);

        $data = $invitations->getCollection()->map(function ($invitation) {
            return [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'role' => $invitation->role,
                'invited_by' => [
                    'id' => $invitation->inviter->id,
                    'name' => $invitation->inviter->name,
                    'email' => $invitation->inviter->email,
                ],
                'expires_at' => $invitation->expires_at->toIso8601String(),
                'created_at' => $invitation->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => $invitations->currentPage(),
                'last_page' => $invitations->lastPage(),
                'per_page' => $invitations->perPage(),
                'total' => $invitations->total(),
            ],
        ]);
    }

    /**
     * Remove member from workspace
     */
    public function removeMember(Organization $organization, User $user): JsonResponse
    {
        $this->authorize('update', $organization);

        // Prevent removing the last owner
        $ownerCount = $organization->memberships()->where('role', 'owner')->count();
        $membership = $organization->memberships()->where('user_id', $user->id)->first();

        if ($membership && $membership->role === 'owner' && $ownerCount <= 1) {
            return response()->json([
                'message' => 'Cannot remove the last owner from the workspace',
            ], 422);
        }

        $this->organizationService->removeMember($organization, $user);

        return response()->json([
            'message' => 'Member removed successfully',
        ]);
    }

    /**
     * Update member role in workspace
     */
    public function updateMemberRole(Request $request, Organization $organization, User $user): JsonResponse
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'role' => ['required', 'in:owner,member'],
        ]);

        // Prevent changing role of last owner
        $membership = $organization->memberships()->where('user_id', $user->id)->first();
        if ($membership && $membership->role === 'owner' && $validated['role'] !== 'owner') {
            $ownerCount = $organization->memberships()->where('role', 'owner')->count();
            if ($ownerCount <= 1) {
                return response()->json([
                    'message' => 'Cannot change role of the last owner',
                ], 422);
            }
        }

        $this->organizationService->updateMemberRole($organization, $user, $validated['role']);

        return response()->json([
            'message' => 'Member role updated successfully',
        ]);
    }
}
