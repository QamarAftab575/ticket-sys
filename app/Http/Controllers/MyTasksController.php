<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetTasksRequest;
use App\Services\MyTasksService;
use App\Services\MyTasksSectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MyTasksController extends Controller
{
    public function __construct(
        private MyTasksService $myTasksService,
        private MyTasksSectionService $sectionService,
    ) {
    }

    /**
     * Display the My Tasks page.
     */
    public function index(): Response
    {
        $user       = auth()->user();
        $preference = $this->myTasksService->getViewPreferences($user, 'list');
        $sections   = $this->sectionService->getOrCreateSections($user);
        
        // Get workspace members for assigning tasks
        // Try to get organization from session first
        $organizationId = session('current_organization_id');
        
        // If no session organization, get user's first active organization
        if (!$organizationId) {
            $organization = $user->organizations()
                ->wherePivot('is_active', true)
                ->where('organizations.is_active', true)
                ->first();
            
            if ($organization) {
                $organizationId = $organization->id;
                session(['current_organization_id' => $organizationId]);
            }
        }
        
        // Get workspace members
        $workspaceMembers = collect();
        if ($organizationId) {
            $organization = \App\Models\Organization::find($organizationId);
            if ($organization) {
                $workspaceMembers = $organization->members()
                    ->select('users.id', 'users.name', 'users.email', 'users.avatar')
                    ->get()
                    ->map(fn($member) => [
                        'id'     => $member->id,
                        'name'   => $member->name,
                        'email'  => $member->email,
                        'avatar' => $member->avatar,
                    ]);
            }
        }

        return Inertia::render('MyTasks/Index', [
            'savedPreferences' => $preference ? [
                'sort'               => $preference->sort,
                'grouping'           => $preference->grouping,
                'section_order'      => $preference->section_order,
                'collapsed_sections' => $preference->collapsed_sections,
                'filters'            => $preference->filters,
            ] : null,
            'myTasksSections' => $sections->map(fn($s) => [
                'id'       => $s->id,
                'name'     => $s->name,
                'position' => $s->position,
            ])->values(),
            'workspaceMembers' => $workspaceMembers,
        ]);
    }

    /**
     * Get user's tasks with filters, sort, and grouping.
     */
    public function getTasks(GetTasksRequest $request): JsonResponse
    {
        $filters = $request->validated('filters') ? json_decode($request->validated('filters'), true) : [];
        $sort = $request->validated('sort') ? json_decode($request->validated('sort'), true) : [];
        $grouping = $request->validated('grouping');
        $page = $request->validated('page', 1);
        $perPage = $request->validated('per_page', 50);

        $tasks = $this->myTasksService->getUserTasks(
            auth()->user(),
            $filters,
            $sort,
            $grouping,
            $page,
            $perPage
        );

        return response()->json($tasks);
    }

    /**
     * Save view preferences for My Tasks.
     */
    public function saveViewPreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'view_type'          => 'required|in:list,board,timeline,calendar,files',
            'sort'               => 'nullable|string',
            'grouping'           => 'nullable|string',
            'section_order'      => 'nullable|string',
            'collapsed_sections' => 'nullable|string',
            'filters'            => 'nullable|string',
        ]);

        $preference = $this->myTasksService->saveViewPreferences(
            auth()->user(),
            $validated
        );

        return response()->json(['success' => true, 'preference' => $preference]);
    }

    /**
     * Get view preferences for My Tasks.
     */
    public function getViewPreferences(string $viewType): JsonResponse
    {
        $preference = $this->myTasksService->getViewPreferences(
            auth()->user(),
            $viewType
        );

        return response()->json($preference ?? []);
    }

    /**
     * Create a task for My Tasks.
     */
    public function storeTask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'section_id'  => 'nullable|uuid|exists:sections,id',
            'status'      => 'nullable|string|in:to_do,in_progress,in_review,complete,blocked',
            'priority'    => 'nullable|string|in:low,medium,high,urgent',
            'due_date'    => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        try {
            if (!isset($validated['priority']) || $validated['priority'] === null) {
                $validated['priority'] = 'medium';
            }

            // If no section given, assign to the user's first My Tasks section
            if (empty($validated['section_id'])) {
                $firstSection = $this->sectionService->getOrCreateSections(auth()->user())->first();
                $validated['section_id'] = $firstSection?->id;
            }

            $task = $this->myTasksService->createTask(auth()->user(), $validated);

            return response()->json(['data' => $task], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // ── My Tasks Sections ────────────────────────────────────────────────────

    /**
     * List all My Tasks sections for the authenticated user.
     */
    public function getSections(): JsonResponse
    {
        $sections = $this->sectionService->getOrCreateSections(auth()->user());

        return response()->json(['data' => $sections->values()]);
    }

    /**
     * Create a new My Tasks section.
     */
    public function storeSection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $section = $this->sectionService->createSection(auth()->user(), $validated['name']);

        return response()->json(['data' => $section], 201);
    }

    /**
     * Rename a My Tasks section.
     */
    public function updateSection(Request $request, string $sectionId): JsonResponse
    {
        $section = \App\Models\Section::findOrFail($sectionId);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $section = $this->sectionService->renameSection(auth()->user(), $section, $validated['name']);

        return response()->json(['data' => $section]);
    }

    /**
     * Delete a My Tasks section.
     */
    public function destroySection(string $sectionId): JsonResponse
    {
        $section = \App\Models\Section::findOrFail($sectionId);
        $this->sectionService->deleteSection(auth()->user(), $section);

        return response()->json(null, 204);
    }

    /**
     * Reorder My Tasks sections.
     */
    public function reorderSections(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'section_ids'   => 'required|array',
            'section_ids.*' => 'uuid|exists:sections,id',
        ]);

        $sections = $this->sectionService->reorderSections(auth()->user(), $validated['section_ids']);

        return response()->json(['data' => $sections->values()]);
    }
}