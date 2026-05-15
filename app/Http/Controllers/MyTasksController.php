<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyTasksController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Get all tasks assigned to the current user across all projects.
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        // Get all tasks assigned to the current user
        $query = Task::where('assignee_id', $user->id)
            ->whereHas('project', function ($q) use ($user) {
                // Only include tasks from projects the user is a member of
                $q->whereHas('members', function ($q2) use ($user) {
                    $q2->where('user_id', $user->id);
                });
            });

        // Apply filters if provided
        $filters = $request->query('filters', []);
        if (!empty($filters)) {
            $query = $this->taskService->applyFilters($query, $filters);
        }

        // Apply sorting if provided
        $sortCriteria = $request->query('sort', []);
        if (!empty($sortCriteria)) {
            $query = $this->taskService->applySortCriteria($query, $sortCriteria);
        }

        $tasks = $query->with([
            'project:id,name',
            'assignee:id,name,email',
            'creator:id,name,email',
            'section:id,name',
            'tags:id,name,color',
            'subtasks',
            'dependencies',
            'dependents',
        ])->get();

        return response()->json([
            'data' => $tasks,
        ]);
    }
}
