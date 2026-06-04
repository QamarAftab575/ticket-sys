<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\ProjectMemberService;
use App\Services\ProjectService;
use App\Services\SectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectApiController extends Controller
{
    protected ProjectService $projectService;
    protected ProjectMemberService $memberService;
    protected SectionService $sectionService;
    protected ActivityLogService $activityService;

    public function __construct(
        ProjectService $projectService,
        ProjectMemberService $memberService,
        SectionService $sectionService,
        ActivityLogService $activityService
    ) {
        $this->projectService = $projectService;
        $this->memberService = $memberService;
        $this->sectionService = $sectionService;
        $this->activityService = $activityService;
    }

    /**
     * Get all projects (filtered by workspace if provided)
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = Project::visibleTo($user);

        // Filter by workspace/organization
        if ($request->has('workspace_id')) {
            $query->where('organization_id', $request->input('workspace_id'));
        }

        // Filter by archived status
        if ($request->has('archived')) {
            $archived = filter_var($request->input('archived'), FILTER_VALIDATE_BOOLEAN);
            if ($archived) {
                $query->whereNotNull('archived_at');
            } else {
                $query->whereNull('archived_at');
            }
        }

        $perPage = $request->input('per_page', 50);
        $perPage = min($perPage, 100);

        $projects = $query->with([
            'organization:id,name',
            'manager:id,name,email,avatar',
            'owner:id,name,email,avatar',
        ])->paginate($perPage);

        $data = $projects->getCollection()->map(fn($project) => $this->formatProject($project));

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
                'from' => $projects->firstItem(),
                'to' => $projects->lastItem(),
            ],
        ]);
    }

    /**
     * Create a new project
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'uuid', 'exists:organizations,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'manager_id' => ['required', 'uuid', 'exists:users,id'],
            'status' => ['sometimes', 'in:on_track,at_risk,off_track,complete'],
            'visibility' => ['sometimes', 'in:public_to_team,public_to_organization,private'],
            'privacy' => ['sometimes', 'in:public_to_team,private,specific_members'],
            'start_date' => ['nullable', 'date'],
            'target_date' => ['nullable', 'date'],
            'color' => ['nullable', 'string'],
            'icon' => ['nullable', 'string'],
            'owner_id' => ['sometimes', 'uuid', 'exists:users,id'],
            'member_ids' => ['sometimes', 'array'],
            'member_ids.*' => ['uuid', 'exists:users,id'],
        ]);

        $user = Auth::user();
        $project = $this->projectService->createProject($validated, $user);

        return response()->json([
            'message' => 'Project created successfully',
            'data' => $this->formatProject($project->load(['organization', 'manager', 'owner'])),
        ], 201);
    }

    /**
     * Get project details
     */
    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->load([
            'organization:id,name',
            'manager:id,name,email,avatar',
            'owner:id,name,email,avatar',
            'creator:id,name,email,avatar',
        ]);

        return response()->json([
            'data' => $this->formatProject($project, true),
        ]);
    }

    /**
     * Update project
     */
    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:on_track,at_risk,off_track,complete'],
            'visibility' => ['sometimes', 'in:public_to_team,public_to_organization,private'],
            'start_date' => ['nullable', 'date'],
            'target_date' => ['nullable', 'date'],
            'color' => ['nullable', 'string'],
            'icon' => ['nullable', 'string'],
        ]);

        $project = $this->projectService->updateProject($project, $validated);

        return response()->json([
            'message' => 'Project updated successfully',
            'data' => $this->formatProject($project),
        ]);
    }

    /**
     * Delete project
     */
    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $this->projectService->deleteProject($project, Auth::user());

        return response()->json([
            'message' => 'Project deleted successfully',
        ], 204);
    }

    /**
     * Archive project
     */
    public function archive(Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $project = $this->projectService->archiveProject($project, Auth::user());

        return response()->json([
            'message' => 'Project archived successfully',
            'data' => $this->formatProject($project),
        ]);
    }

    /**
     * Unarchive project
     */
    public function unarchive(Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $project = $this->projectService->unarchiveProject($project, Auth::user());

        return response()->json([
            'message' => 'Project unarchived successfully',
            'data' => $this->formatProject($project),
        ]);
    }

    /**
     * Duplicate project
     */
    public function duplicate(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $validated = $request->validate([
            'copy_tasks' => ['sometimes', 'boolean'],
            'copy_members' => ['sometimes', 'boolean'],
        ]);

        $newProject = $this->projectService->duplicateProject(
            $project,
            Auth::user(),
            $validated
        );

        return response()->json([
            'message' => 'Project duplicated successfully',
            'data' => $this->formatProject($newProject->load(['organization', 'manager', 'owner'])),
        ], 201);
    }

    /**
     * Get project members
     */
    public function members(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $perPage = request()->input('per_page', 50);
        $perPage = min($perPage, 100);

        $members = $project->members()
            ->withPivot(['role', 'assigned_at', 'assigned_by'])
            ->paginate($perPage);

        $data = $members->getCollection()->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'avatar' => $member->avatar,
                'role' => $member->pivot->role,
                'assigned_at' => $member->pivot->assigned_at,
            ];
        });

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => $members->currentPage(),
                'last_page' => $members->lastPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
            ],
        ]);
    }

    /**
     * Get project users (alias for members)
     */
    public function users(Project $project): JsonResponse
    {
        return $this->members($project);
    }

    /**
     * Add member to project
     */
    public function addMember(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'role' => ['required', 'in:project_admin,editor,commenter,viewer'],
        ]);

        $user = User::findOrFail($validated['user_id']);

        $this->memberService->addMember(
            $project,
            $user,
            Auth::user(),
            $validated['role'],
            'direct_invite'
        );

        return response()->json([
            'message' => 'Member added successfully',
        ], 201);
    }

    /**
     * Remove member from project
     */
    public function removeMember(Project $project, User $user): JsonResponse
    {
        $this->authorize('update', $project);

        $this->memberService->removeMember($project, $user, Auth::user());

        return response()->json([
            'message' => 'Member removed successfully',
        ]);
    }

    /**
     * Update member role
     */
    public function updateMemberRole(Request $request, Project $project, User $user): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'role' => ['required', 'in:project_admin,editor,commenter,viewer'],
        ]);

        $this->memberService->updateMemberRole($project, $user, $validated['role']);

        return response()->json([
            'message' => 'Member role updated successfully',
        ]);
    }

    /**
     * Get project activity
     */
    public function activity(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $activities = $project->activities()
            ->with('user:id,name,email,avatar')
            ->orderByDesc('created_at')
            ->paginate(20);

        $data = $activities->getCollection()->map(function ($activity) {
            return [
                'id' => $activity->id,
                'action' => $activity->action,
                'user' => [
                    'id' => $activity->user->id,
                    'name' => $activity->user->name,
                    'email' => $activity->user->email,
                    'avatar' => $activity->user->avatar,
                ],
                'old_value' => $activity->old_value,
                'new_value' => $activity->new_value,
                'created_at' => $activity->created_at,
            ];
        });

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'per_page' => $activities->perPage(),
                'total' => $activities->total(),
            ],
        ]);
    }

    /**
     * Get project sections
     */
    public function sections(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $sections = $project->sections()->orderBy('order')->get();

        $data = $sections->map(function ($section) {
            return [
                'id' => $section->id,
                'name' => $section->name,
                'description' => $section->description,
                'order' => $section->order,
                'created_at' => $section->created_at->toIso8601String(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Format project for response
     */
    private function formatProject(Project $project, bool $detailed = false): array
    {
        $data = [
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
            'status' => $project->status,
            'visibility' => $project->visibility,
            'privacy' => $project->privacy,
            'color' => $project->color,
            'icon' => $project->icon,
            'start_date' => $project->start_date,
            'target_date' => $project->target_date,
            'archived_at' => $project->archived_at?->toIso8601String(),
            'created_at' => $project->created_at->toIso8601String(),
            'updated_at' => $project->updated_at->toIso8601String(),
        ];

        if ($project->relationLoaded('organization')) {
            $data['organization'] = [
                'id' => $project->organization->id,
                'name' => $project->organization->name,
            ];
        }

        if ($project->relationLoaded('manager')) {
            $data['manager'] = $project->manager ? [
                'id' => $project->manager->id,
                'name' => $project->manager->name,
                'email' => $project->manager->email,
                'avatar' => $project->manager->avatar,
            ] : null;
        }

        if ($project->relationLoaded('owner')) {
            $data['owner'] = $project->owner ? [
                'id' => $project->owner->id,
                'name' => $project->owner->name,
                'email' => $project->owner->email,
                'avatar' => $project->owner->avatar,
            ] : null;
        }

        if ($detailed && $project->relationLoaded('creator')) {
            $data['creator'] = $project->creator ? [
                'id' => $project->creator->id,
                'name' => $project->creator->name,
                'email' => $project->creator->email,
                'avatar' => $project->creator->avatar,
            ] : null;
        }

        return $data;
    }
}
