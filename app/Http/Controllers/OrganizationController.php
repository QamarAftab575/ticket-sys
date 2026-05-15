<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Models\Organization;
use App\Services\OrganizationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrganizationController extends Controller
{
    public function __construct(private OrganizationService $organizationService)
    {
    }

    /**
     * Display a listing of organizations for the authenticated user.
     */
    public function index()
    {
        $organizations = auth()->user()->organizations()
            ->where('organizations.is_active', true)
            ->whereNull('organization_memberships.deleted_at')
            ->get();

        return Inertia::render('Organizations/Index', [
            'organizations' => $organizations,
        ]);
    }

    /**
     * Show the form for creating a new organization.
     */
    public function create()
    {
        return Inertia::render('Organizations/Create');
    }

    /**
     * Store a newly created organization in storage.
     */
    public function store(StoreOrganizationRequest $request)
    {
        $user = auth()->user();
        $validated = $request->validated();

        $organization = $this->organizationService->createOrganization($validated, $user);
        $this->organizationService->addMember($organization, $user, 'workspace_owner');

        return redirect()->route('organizations.show', $organization)
            ->with('status', 'Organization created successfully!');
    }

    /**
     * Display the specified organization.
     */
    public function show(Organization $organization)
    {
        // Check if user has access
        if (!$this->organizationService->canUserAccess(auth()->user(), $organization)) {
            abort(403, 'You do not have access to this organization.');
        }

        $members = $organization->members()
            ->with('organizationMemberships')
            ->get();

        return Inertia::render('Organizations/Show', [
            'organization' => $organization,
            'members' => $members,
            'userRole' => $organization->getMemberRole(auth()->user()),
        ]);
    }

    /**
     * Show the form for editing the specified organization.
     */
    public function edit(Organization $organization)
    {
        // Check if user is admin
        if (!$this->organizationService->canUserManage(auth()->user(), $organization)) {
            abort(403, 'You do not have permission to edit this organization.');
        }

        return Inertia::render('Organizations/Edit', [
            'organization' => $organization,
        ]);
    }

    /**
     * Update the specified organization in storage.
     */
    public function update(UpdateOrganizationRequest $request, Organization $organization)
    {
        // Check if user is admin
        if (!$this->organizationService->canUserManage(auth()->user(), $organization)) {
            abort(403, 'You do not have permission to update this organization.');
        }

        $validated = $request->validated();
        $this->organizationService->updateOrganization($organization, $validated);

        return redirect()->route('organizations.show', $organization)
            ->with('status', 'Organization updated successfully!');
    }

    /**
     * Remove the specified organization from storage.
     */
    public function destroy(Organization $organization)
    {
        // Check if user is owner
        if (!$organization->isOwner(auth()->user())) {
            abort(403, 'Only organization owner can delete this organization.');
        }

        $this->organizationService->deleteOrganization($organization);

        return redirect()->route('organizations.index')
            ->with('status', 'Organization deleted successfully!');
    }
}
