<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddOrganizationMemberRequest;
use App\Http\Requests\UpdateOrganizationMemberRequest;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;
use App\Services\OrganizationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrganizationMemberController extends Controller
{
    public function __construct(private OrganizationService $organizationService)
    {
    }

    /**
     * Display a listing of members for the organization.
     */
    public function index(Organization $organization)
    {
        // Check if user has access
        if (!$this->organizationService->canUserAccess(auth()->user(), $organization)) {
            abort(403, 'You do not have access to this organization.');
        }

        $members = $organization->members()
            ->with('organizationMemberships')
            ->get();

        return Inertia::render('Organizations/Members/Index', [
            'organization' => $organization,
            'members' => $members,
            'userRole' => $organization->getMemberRole(auth()->user()),
        ]);
    }

    /**
     * Store a newly created member in the organization.
     */
    public function store(AddOrganizationMemberRequest $request, Organization $organization)
    {
        // Check if user is admin
        if (!$this->organizationService->canUserManage(auth()->user(), $organization)) {
            abort(403, 'You do not have permission to add members to this organization.');
        }

        $validated = $request->validated();
        $user = User::where('email', $validated['email'])->firstOrFail();

        // Check if user is already a member
        if ($organization->hasMember($user)) {
            return back()->withErrors(['email' => 'User is already a member of this organization.']);
        }

        $this->organizationService->addMember($organization, $user, $validated['role']);

        return back()->with('status', 'Member added successfully!');
    }

    /**
     * Update the specified member's role in the organization.
     */
    public function update(UpdateOrganizationMemberRequest $request, Organization $organization, User $user)
    {
        // Check if user is admin
        if (!$this->organizationService->canUserManage(auth()->user(), $organization)) {
            abort(403, 'You do not have permission to update members in this organization.');
        }

        // Check if user is a member
        if (!$organization->hasMember($user)) {
            abort(404, 'User is not a member of this organization.');
        }

        // Prevent changing owner role
        if ($organization->isOwner($user)) {
            return back()->withErrors(['role' => 'Cannot change the role of the organization owner.']);
        }

        $validated = $request->validated();
        $this->organizationService->updateMemberRole($organization, $user, $validated['role']);

        return back()->with('status', 'Member role updated successfully!');
    }

    /**
     * Remove the specified member from the organization.
     */
    public function destroy(Organization $organization, User $user)
    {
        // Check if user is admin
        if (!$this->organizationService->canUserManage(auth()->user(), $organization)) {
            abort(403, 'You do not have permission to remove members from this organization.');
        }

        // Check if user is a member
        if (!$organization->hasMember($user)) {
            abort(404, 'User is not a member of this organization.');
        }

        // Prevent removing the owner
        if ($organization->isOwner($user)) {
            return back()->withErrors(['user' => 'Cannot remove the organization owner.']);
        }

        $this->organizationService->removeMember($organization, $user);

        return back()->with('status', 'Member removed successfully!');
    }

    /**
     * Deactivate a member in the organization.
     */
    public function deactivate(OrganizationMembership $membership)
    {
        try {
            $this->authorize('update', $membership->organization);

            $membership->deactivate();

            return response()->json([
                'message' => 'Member deactivated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Activate a member in the organization.
     */
    public function activate(OrganizationMembership $membership)
    {
        try {
            $this->authorize('update', $membership->organization);

            $membership->activate();

            return response()->json([
                'message' => 'Member activated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
