<?php

namespace App\Http\Controllers;

use App\Helpers\BillingHelper;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function __construct(private ProjectService $projectService)
    {
    }

    /**
     * Display a listing of projects.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $projects = Project::visibleTo($user)
            ->with('members')
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->paginate(15);

        // Get user workspaces
        $userWorkspaces = $user->organizations()
            ->where('organizations.is_active', true)
            ->whereNull('organization_memberships.deleted_at')
            ->select('organizations.id', 'organizations.name', 'organizations.avatar_color')
            ->get();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters' => $request->only(['status', 'search']),
            'userWorkspaces' => $userWorkspaces,
            'currentWorkspace' => null,
            'userRole' => 'member',
        ]);
    }

    /**
     * Store a newly created project.
     */
    public function store(CreateProjectRequest $request)
    {
        Gate::authorize('create', Project::class);

        $user = auth()->user();
        $organization = BillingHelper::getActiveWorkspace($user);

        if (!$organization) {
            return redirect()->back()
                ->with('error', 'You must belong to an organization to create a project.');
        }

        // Check if user has quota to create a project
        if (!BillingHelper::canCreateProject($organization)) {
            $errorMessage = BillingHelper::isTemplateMode() 
                ? 'This application is in demo mode. Creating projects is restricted.'
                : 'Your project quota has been reached. Please upgrade your plan to create more projects.';
            
            return redirect()->route('projects.index')
                ->with('error', $errorMessage);
        }

        $data = array_merge($request->validated(), [
            'manager_id' => $user->id,
            'status'     => 'on_track',
        ]);

        $project = $this->projectService->createProject($data, $user);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        Gate::authorize('view', $project);

        $user = auth()->user();
        $project->load(['members:id,name,email,avatar', 'sections' => fn($q) => $q->orderBy('position')]);

        // Resolve role once — reuse for both canEdit and canManageMembers to avoid extra queries
        $role = $project->projectMembers()
            ->where('user_id', $user->id)
            ->value('role');

        return Inertia::render('Projects/Show', [
            'project'           => $project,
            'canEdit'           => in_array($role, ['project_admin', 'editor']),
            'canManageMembers'  => $role === 'project_admin',
            'currentUser'       => $user->only('id', 'name', 'email', 'avatar'),
        ]);
    }

    /**
     * Show the form for editing the project.
     */
    public function edit(Project $project)
    {
        Gate::authorize('update', $project);

        $project->load('members');
        $projectMembers = $project->members()
            ->select('users.id', 'users.name')
            ->get()
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->name]);

        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'teamMembers' => $projectMembers,
        ]);
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        Gate::authorize('create', Project::class);

        $user = auth()->user();
        $organization = BillingHelper::getActiveWorkspace($user);

        if (!$organization) {
            abort(403, 'You must belong to an organization to create a project.');
        }

        // Check if user has quota to create a project
        if (!BillingHelper::canCreateProject($organization)) {
            $errorMessage = BillingHelper::isTemplateMode() 
                ? 'This application is in demo mode. Creating projects is restricted.'
                : 'Your project quota has been reached. Please upgrade your plan to create more projects.';
            
            return redirect()->route('projects.index')
                ->with('error', $errorMessage);
        }

        $teamMembers = $organization->members()
            ->where('users.id', '!=', $user->id)
            ->get(['users.id', 'users.name'])
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->name]);

        return Inertia::render('Projects/Create', [
            'organizationId' => $organization->id,
            'teamMembers' => $teamMembers,
        ]);
    }

    /**
     * Update the specified project.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        Gate::authorize('update', $project);

        $this->projectService->updateProject($project, $request->validated());

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Delete the specified project.
     */
    public function destroy(Project $project)
    {
        Gate::authorize('delete', $project);

        $this->projectService->deleteProject($project);

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    /**
     * Update project status.
     */
    public function updateStatus(Request $request, Project $project)
    {
        Gate::authorize('changeStatus', $project);

        $request->validate([
            'status'        => 'required|in:on_track,at_risk,off_track,on_hold,complete',
            'status_update' => 'nullable|string|max:1000',
        ]);

        $updated = $this->projectService->updateStatus(
            $project,
            $request->status,
            $request->status_update
        );

        return response()->json([
            'status'        => $updated->status,
            'status_update' => $updated->status_update,
            'updated_at'    => $updated->updated_at,
        ]);
    }

    /**
     * Return share modal data: members, pending invitations, workspace member count.
     * - Only direct_invite members are listed individually
     */
    public function shareData(Project $project)
    {
        Gate::authorize('view', $project);

        // Workspace owner — always shown as fixed project_admin
        $ownerMembership = $project->organization->memberships()
            ->where('role', 'owner')
            ->with('user:id,name,email')
            ->first();

        $workspaceOwner = $ownerMembership ? [
            'id'          => $ownerMembership->user->id,
            'name'        => $ownerMembership->user->name,
            'email'       => $ownerMembership->user->email,
            'role'        => 'project_admin',
            'access_type' => 'workspace_owner',
            'is_fixed'    => true,
        ] : null;

        // Explicitly invited members only (direct_invite), excluding the workspace owner
        $ownerId = $ownerMembership?->user_id;

        $members = $project->projectMembers()
            ->where('access_type', \App\Models\ProjectMember::ACCESS_TYPE_DIRECT_INVITE)
            ->when($ownerId, fn($q) => $q->where('user_id', '!=', $ownerId))
            ->with('user:id,name,email')
            ->get()
            ->map(fn($pm) => [
                'id'          => $pm->user->id,
                'name'        => $pm->user->name,
                'email'       => $pm->user->email,
                'role'        => $pm->role,
                'access_type' => $pm->access_type,
                'is_fixed'    => false,
            ]);

        $invitations = $project->invitations()
            ->pending()
            ->notExpired()
            ->orderBy('created_at', 'desc')
            ->get(['id', 'email', 'role', 'created_at', 'expires_at']);

        $workspaceMemberCount = $project->organization->members()->count();

        return response()->json([
            'workspace_owner'        => $workspaceOwner,
            'members'                => $members,
            'invitations'            => $invitations,
            'workspace_member_count' => $workspaceMemberCount,
            'visibility'             => $project->visibility,
            'workspace_member_role'  => $project->workspace_member_role ?? 'editor',
        ]);
    }

    /**
     * Update project visibility.
     */
    public function updateVisibility(Request $request, Project $project)
    {
        Gate::authorize('changeVisibility', $project);

        $request->validate([
            'visibility' => 'required|in:public_to_team,private_to_members',
        ]);

        $this->projectService->updateVisibility($project, $request->visibility);

        return response()->json(['visibility' => $project->fresh()->visibility]);
    }

    /**
     * Update the default role for workspace members on this project.
     */
    public function updateWorkspaceMemberRole(Request $request, Project $project)
    {
        Gate::authorize('changeVisibility', $project);

        $request->validate([
            'workspace_member_role' => 'required|in:editor,commenter,viewer',
        ]);

        $project->update(['workspace_member_role' => $request->workspace_member_role]);

        return response()->json(['workspace_member_role' => $project->fresh()->workspace_member_role]);
    }

    /**
     * Change project lead.
     */
    public function changeProjectLead(Request $request, Project $project)
    {
        Gate::authorize('changeProjectLead', $project);

        $request->validate([
            'manager_id' => 'required|uuid|exists:users,id',
        ]);

        $newLead = \App\Models\User::findOrFail($request->manager_id);

        // Verify new lead is a workspace member
        $isWorkspaceMember = $project->organization->members()
            ->where('user_id', $newLead->id)
            ->exists();

        if (!$isWorkspaceMember) {
            abort(422, 'The new project lead must be a workspace member.');
        }

        $this->projectService->changeProjectLead($project, $newLead);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project lead changed successfully.');
    }
}
