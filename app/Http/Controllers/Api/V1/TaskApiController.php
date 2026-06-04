<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CustomField;
use App\Models\Project;
use App\Models\Section;
use App\Models\Task;
use App\Services\CustomFieldService;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TaskApiController extends Controller
{
    protected TaskService $taskService;
    protected CustomFieldService $customFieldService;

    public function __construct(TaskService $taskService, CustomFieldService $customFieldService)
    {
        $this->taskService = $taskService;
        $this->customFieldService = $customFieldService;
    }

    /**
     * Get tasks for a project
     */
    public function index(Request $request, Project $project): JsonResponse
    {
        $this->authorize('viewAny', [Task::class, $project]);

        $query = $project->tasks();

        // Apply filters if provided
        $filters = $request->input('filters', []);
        if (!empty($filters)) {
            $query = $this->taskService->applyFilters($query, $filters);
        }

        // Apply sorting if provided
        $sortCriteria = $request->input('sort', []);
        if (!empty($sortCriteria)) {
            $query = $this->taskService->applySortCriteria($query, $sortCriteria);
        }

        $tasks = $query->with([
            'assignee:id,name,email,avatar',
            'creator:id,name,email,avatar',
            'section:id,name',
            'tags:id,name,color',
        ])->withCount('subtasks')->get();

        $data = $tasks->map(fn($task) => $this->formatTask($task));

        return response()->json(['data' => $data]);
    }

    /**
     * Create a new task in project
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorize('create', [Task::class, $project]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'section_id' => ['nullable', 'uuid', 'exists:sections,id'],
            'assignee_id' => ['nullable', 'uuid', 'exists:users,id'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'status' => ['nullable', 'in:not_started,in_progress,completed'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['uuid', 'exists:tags,id'],
        ]);

        try {
            $task = $this->taskService->createTask($project, $validated, Auth::user());

            return response()->json([
                'message' => 'Task created successfully',
                'data' => $this->formatTask($task->load([
                    'assignee:id,name,email,avatar',
                    'creator:id,name,email,avatar',
                    'section:id,name',
                    'tags:id,name,color',
                ])),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Get task details
     */
    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $task->load([
            'assignee:id,name,email,avatar',
            'creator:id,name,email,avatar',
            'section:id,name',
            'tags:id,name,color',
            'subtasks',
            'dependencies' => fn($q) => $q->select('tasks.id', 'tasks.name', 'tasks.status', 'tasks.completed_at', 'tasks.due_date', 'tasks.start_date', 'tasks.assignee_id')->with('assignee:id,name,avatar'),
            'dependents' => fn($q) => $q->select('tasks.id', 'tasks.name', 'tasks.status', 'tasks.completed_at', 'tasks.due_date', 'tasks.start_date', 'tasks.assignee_id')->with('assignee:id,name,avatar'),
            'customFieldValues.customField',
            'attachments',
        ]);

        return response()->json([
            'data' => $this->formatTask($task, true),
        ]);
    }

    /**
     * Update a task
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'section_id' => ['nullable', 'uuid', 'exists:sections,id'],
            'assignee_id' => ['nullable', 'uuid', 'exists:users,id'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'status' => ['nullable', 'in:not_started,in_progress,completed'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['uuid', 'exists:tags,id'],
        ]);

        try {
            $updatedTask = $this->taskService->updateTask($task, $validated);

            return response()->json([
                'message' => 'Task updated successfully',
                'data' => $this->formatTask($updatedTask->load([
                    'assignee:id,name,email,avatar',
                    'creator:id,name,email,avatar',
                    'section:id,name',
                    'tags:id,name,color',
                ])),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Delete a task
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $this->taskService->deleteTask($task);

        return response()->json([
            'message' => 'Task deleted successfully',
        ], 204);
    }

    /**
     * Mark task as complete
     */
    public function complete(Task $task): JsonResponse
    {
        $this->authorize('complete', $task);

        try {
            $completedTask = $this->taskService->completeTask($task, Auth::user());

            return response()->json([
                'message' => 'Task completed successfully',
                'data' => $this->formatTask($completedTask->load([
                    'assignee:id,name,email,avatar',
                    'creator:id,name,email,avatar',
                    'completedBy:id,name,email',
                ])),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Reopen a completed task
     */
    public function reopen(Task $task): JsonResponse
    {
        $this->authorize('complete', $task);

        try {
            $reopenedTask = $this->taskService->reopenTask($task);

            return response()->json([
                'message' => 'Task reopened successfully',
                'data' => $this->formatTask($reopenedTask->load([
                    'assignee:id,name,email,avatar',
                    'creator:id,name,email,avatar',
                ])),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Duplicate a task
     */
    public function duplicate(Task $task): JsonResponse
    {
        if ($task->project === null) {
            if ($task->creator_id !== Auth::id() && $task->assignee_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        } else {
            $this->authorize('create', [Task::class, $task->project]);
        }

        try {
            $duplicatedTask = $this->taskService->duplicateTask($task);

            return response()->json([
                'message' => 'Task duplicated successfully',
                'data' => $this->formatTask($duplicatedTask->load([
                    'assignee:id,name,email,avatar',
                    'creator:id,name,email,avatar',
                    'section:id,name',
                    'tags:id,name,color',
                ])),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Move task to different section/position
     */
    public function move(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'section_id' => ['nullable', 'uuid', 'exists:sections,id'],
            'position' => ['nullable', 'integer', 'min:0'],
            'my_tasks_section_id' => ['nullable', 'uuid', 'exists:sections,id'],
            'my_tasks_position' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            // Check if this is a My Tasks move
            if (isset($validated['my_tasks_section_id'])) {
                $myTasksSection = Section::findOrFail($validated['my_tasks_section_id']);

                if (!$myTasksSection->is_my_tasks || $myTasksSection->user_id !== Auth::id()) {
                    return response()->json(['message' => 'Invalid My Tasks section'], 422);
                }

                $movedTask = $this->taskService->moveTaskInMyTasks(
                    $task,
                    $myTasksSection,
                    $validated['my_tasks_position'] ?? null
                );
            } elseif (isset($validated['section_id'])) {
                $section = Section::findOrFail($validated['section_id']);
                $movedTask = $this->taskService->moveTask($task, $section, $validated['position'] ?? null);
            } else {
                $movedTask = $this->taskService->repositionTask($task, $validated['position'] ?? null);
            }

            return response()->json([
                'message' => 'Task moved successfully',
                'data' => $this->formatTask($movedTask->load([
                    'assignee:id,name,email,avatar',
                    'section:id,name',
                    'myTasksSection:id,name',
                    'tags:id,name,color',
                ])),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get subtasks
     */
    public function subtasks(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $subtasks = $task->subtasks()
            ->with('assignee:id,name,email,avatar')
            ->orderBy('position')
            ->orderBy('created_at')
            ->get();

        $data = $subtasks->map(fn($subtask) => $this->formatTask($subtask));

        return response()->json(['data' => $data]);
    }

    /**
     * Create subtask
     */
    public function createSubtask(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'assignee_id' => ['nullable', 'uuid', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:not_started,in_progress,completed'],
        ]);

        try {
            $subtask = $this->taskService->createSubtask($task, $validated, Auth::user());

            return response()->json([
                'message' => 'Subtask created successfully',
                'data' => $this->formatTask($subtask->load('assignee:id,name,email,avatar')),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Add dependency
     */
    public function addDependency(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'depends_on_task_id' => ['required', 'uuid', 'exists:tasks,id'],
            'type' => ['required', 'in:blocked_by,blocks'],
        ]);

        $dependsOn = Task::findOrFail($validated['depends_on_task_id']);

        try {
            $this->taskService->addDependency($task, $dependsOn, $validated['type']);

            return response()->json([
                'message' => 'Dependency added successfully',
                'data' => $this->formatTask($task->load([
                    'dependencies' => fn($q) => $q->select('tasks.id', 'tasks.name', 'tasks.status', 'tasks.completed_at', 'tasks.due_date', 'tasks.start_date', 'tasks.assignee_id')->with('assignee:id,name,avatar'),
                    'dependents' => fn($q) => $q->select('tasks.id', 'tasks.name', 'tasks.status', 'tasks.completed_at', 'tasks.due_date', 'tasks.start_date', 'tasks.assignee_id')->with('assignee:id,name,avatar'),
                ])),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Remove dependency
     */
    public function removeDependency(Task $task, Task $dependsOnTask): JsonResponse
    {
        $this->authorize('update', $task);

        $this->taskService->removeDependency($task, $dependsOnTask);

        return response()->json([
            'message' => 'Dependency removed successfully',
            'data' => $this->formatTask($task->load([
                'dependencies' => fn($q) => $q->select('tasks.id', 'tasks.name', 'tasks.status', 'tasks.completed_at', 'tasks.due_date', 'tasks.start_date', 'tasks.assignee_id')->with('assignee:id,name,avatar'),
                'dependents' => fn($q) => $q->select('tasks.id', 'tasks.name', 'tasks.status', 'tasks.completed_at', 'tasks.due_date', 'tasks.start_date', 'tasks.assignee_id')->with('assignee:id,name,avatar'),
            ])),
        ]);
    }

    /**
     * Get task activities
     */
    public function activities(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $activities = $task->activities()
            ->with('user:id,name,email,avatar')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $items = collect($activities->items())->map(function ($activity) {
            $data = $activity->toArray();
            $data['created_at'] = $activity->getRawOriginal('created_at')
                ? \Carbon\Carbon::parse($activity->getRawOriginal('created_at'), 'UTC')->toIso8601ZuluString()
                : null;
            return $data;
        });

        return response()->json([
            'data' => $items,
            'pagination' => [
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'per_page' => $activities->perPage(),
                'total' => $activities->total(),
                'has_more' => $activities->hasMorePages(),
            ],
        ]);
    }

    /**
     * Set custom field value
     */
    public function setCustomFieldValue(Request $request, Task $task, CustomField $customField): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'value' => ['nullable'],
        ]);

        try {
            if ($customField->project_id !== $task->project_id) {
                return response()->json([
                    'message' => 'Custom field does not belong to this project',
                ], 403);
            }

            $this->customFieldService->setFieldValue($task, $customField, $validated['value']);

            return response()->json([
                'message' => 'Custom field value updated successfully',
                'data' => [
                    'task_id' => $task->id,
                    'custom_field_id' => $customField->id,
                    'value' => $validated['value'],
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update custom field value: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search tasks
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2'],
            'project_id' => ['nullable', 'uuid', 'exists:projects,id'],
            'workspace_id' => ['nullable', 'uuid', 'exists:organizations,id'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $user = Auth::user();
        $query = Task::query();

        // Search by name or description
        $query->where(function ($q) use ($validated) {
            $q->where('name', 'like', "%{$validated['query']}%")
                ->orWhere('description', 'like', "%{$validated['query']}%");
        });

        // Filter by project
        if (isset($validated['project_id'])) {
            $query->where('project_id', $validated['project_id']);
        }

        // Filter by workspace
        if (isset($validated['workspace_id'])) {
            $query->whereHas('project', function ($q) use ($validated) {
                $q->where('organization_id', $validated['workspace_id']);
            });
        }

        // Only show tasks user has access to
        $query->whereHas('project', function ($q) use ($user) {
            $q->visibleTo($user);
        });

        $limit = $validated['limit'] ?? 10;
        $tasks = $query->with([
            'assignee:id,name,email,avatar',
            'section:id,name',
            'project:id,name',
        ])->limit($limit)->get();

        $data = $tasks->map(fn($task) => $this->formatTask($task));

        return response()->json(['data' => $data]);
    }

    /**
     * Format task for response
     */
    private function formatTask(Task $task, bool $detailed = false): array
    {
        $data = [
            'id' => $task->id,
            'name' => $task->name,
            'description' => $task->description,
            'status' => $task->status,
            'priority' => $task->priority,
            'start_date' => $task->start_date,
            'due_date' => $task->due_date,
            'completed_at' => $task->completed_at?->toIso8601String(),
            'position' => $task->position,
            'created_at' => $task->created_at->toIso8601String(),
            'updated_at' => $task->updated_at->toIso8601String(),
        ];

        if ($task->relationLoaded('assignee')) {
            $data['assignee'] = $task->assignee ? [
                'id' => $task->assignee->id,
                'name' => $task->assignee->name,
                'email' => $task->assignee->email,
                'avatar' => $task->assignee->avatar,
            ] : null;
        }

        if ($task->relationLoaded('creator')) {
            $data['creator'] = $task->creator ? [
                'id' => $task->creator->id,
                'name' => $task->creator->name,
                'email' => $task->creator->email,
                'avatar' => $task->creator->avatar,
            ] : null;
        }

        if ($task->relationLoaded('section')) {
            $data['section'] = $task->section ? [
                'id' => $task->section->id,
                'name' => $task->section->name,
            ] : null;
        }

        if ($task->relationLoaded('project')) {
            $data['project'] = $task->project ? [
                'id' => $task->project->id,
                'name' => $task->project->name,
            ] : null;
        }

        if ($task->relationLoaded('tags')) {
            $data['tags'] = $task->tags->map(fn($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'color' => $tag->color,
            ]);
        }

        if (isset($task->subtasks_count)) {
            $data['subtasks_count'] = $task->subtasks_count;
        }

        if ($detailed) {
            if ($task->relationLoaded('subtasks')) {
                $data['subtasks'] = $task->subtasks->map(fn($subtask) => $this->formatTask($subtask));
            }

            if ($task->relationLoaded('dependencies')) {
                $data['dependencies'] = $task->dependencies->map(fn($dep) => [
                    'id' => $dep->id,
                    'name' => $dep->name,
                    'status' => $dep->status,
                    'completed_at' => $dep->completed_at?->toIso8601String(),
                    'due_date' => $dep->due_date,
                    'assignee' => $dep->relationLoaded('assignee') && $dep->assignee ? [
                        'id' => $dep->assignee->id,
                        'name' => $dep->assignee->name,
                        'avatar' => $dep->assignee->avatar,
                    ] : null,
                ]);
            }

            if ($task->relationLoaded('dependents')) {
                $data['dependents'] = $task->dependents->map(fn($dep) => [
                    'id' => $dep->id,
                    'name' => $dep->name,
                    'status' => $dep->status,
                    'completed_at' => $dep->completed_at?->toIso8601String(),
                    'due_date' => $dep->due_date,
                    'assignee' => $dep->relationLoaded('assignee') && $dep->assignee ? [
                        'id' => $dep->assignee->id,
                        'name' => $dep->assignee->name,
                        'avatar' => $dep->assignee->avatar,
                    ] : null,
                ]);
            }

            if ($task->relationLoaded('customFieldValues')) {
                $data['custom_field_values'] = $task->customFieldValues->map(fn($cfv) => [
                    'id' => $cfv->id,
                    'custom_field_id' => $cfv->custom_field_id,
                    'custom_field' => $cfv->relationLoaded('customField') && $cfv->customField ? [
                        'id' => $cfv->customField->id,
                        'name' => $cfv->customField->name,
                        'type' => $cfv->customField->type,
                    ] : null,
                    'value' => $cfv->value,
                ]);
            }

            if ($task->relationLoaded('attachments')) {
                $data['attachments'] = $task->attachments->map(fn($attachment) => [
                    'id' => $attachment->id,
                    'filename' => $attachment->filename,
                    'mime_type' => $attachment->mime_type,
                    'size' => $attachment->size,
                    'url' => $attachment->url,
                    'created_at' => $attachment->created_at->toIso8601String(),
                ]);
            }
        }

        return $data;
    }
}
