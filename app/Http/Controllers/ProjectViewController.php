<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetTasksRequest;
use App\Http\Requests\SaveViewPreferencesRequest;
use App\Models\Project;
use App\Services\ProjectViewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectViewController extends Controller
{
    public function __construct(private ProjectViewService $projectViewService)
    {
    }

    /**
     * Get tasks with filters, sort, and grouping.
     */
    public function getTasks(Project $project, GetTasksRequest $request): JsonResponse
    {
        $this->authorize('view', $project);

        $filters = $request->validated('filters') ? json_decode($request->validated('filters'), true) : [];
        $sort = $request->validated('sort') ? json_decode($request->validated('sort'), true) : [];
        $grouping = $request->validated('grouping');
        $page = $request->validated('page', 1);
        $perPage = $request->validated('per_page', 50);

        $tasks = $this->projectViewService->getTasks(
            $project,
            $filters,
            $sort,
            $grouping,
            $page,
            $perPage
        );

        return response()->json($tasks);
    }

    /**
     * Get project files.
     */
    public function getFiles(Project $project, Request $request): JsonResponse
    {
        $this->authorize('view', $project);

        $search = $request->query('search');
        $type = $request->query('type');
        $uploadedBy = $request->query('uploaded_by');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 50);

        $files = $this->projectViewService->getProjectFiles(
            $project,
            $search,
            $type,
            $uploadedBy,
            $dateFrom,
            $dateTo,
            $page,
            $perPage
        );

        return response()->json($files);
    }

    /**
     * Get dashboard data with optional filters.
     */
    public function getDashboardData(Project $project, Request $request): JsonResponse
    {
        $this->authorize('view', $project);

        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $memberIds = $request->query('member_ids') ? explode(',', $request->query('member_ids')) : [];

        $data = $this->projectViewService->getDashboardData(
            $project,
            $dateFrom,
            $dateTo,
            $memberIds
        );

        return response()->json($data);
    }

    /**
     * Save view preferences.
     */
    public function saveViewPreferences(Project $project, SaveViewPreferencesRequest $request): JsonResponse
    {
        $this->authorize('view', $project);

        $preference = $this->projectViewService->saveViewPreferences(
            $project,
            auth()->user(),
            $request->validated()
        );

        return response()->json(['success' => true, 'preference' => $preference]);
    }

    /**
     * Get view preferences.
     */
    public function getViewPreferences(Project $project, string $viewType): JsonResponse
    {
        $this->authorize('view', $project);

        $preference = $this->projectViewService->getViewPreferences(
            $project,
            auth()->user(),
            $viewType
        );

        return response()->json($preference ?? []);
    }
}
