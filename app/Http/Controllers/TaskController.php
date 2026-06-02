<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoveTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\CustomField;
use App\Models\Project;
use App\Models\Section;
use App\Models\Task;
use App\Services\CustomFieldService;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    protected TaskService $taskService;
    protected CustomFieldService $customFieldService;

    public function __construct(TaskService $taskService, CustomFieldService $customFieldService)
    {
        $this->taskService = $taskService;
        $this->customFieldService = $customFieldService;
    }

    /**
     * Get all tasks for a project.
     */
    public function index(Project $project): JsonResponse
    {
        $this->authorize('viewAny', [Task::class, $project]);

        $query = $project->tasks();

        // Apply filters if provided
        $filters = request()->query('filters', []);
        if (!empty($filters)) {
            $query = $this->taskService->applyFilters($query, $filters);
        }

        // Apply sorting if provided
        $sortCriteria = request()->query('sort', []);
        if (!empty($sortCriteria)) {
            $query = $this->taskService->applySortCriteria($query, $sortCriteria);
        }

        $tasks = $query->with([
            'assignee:id,name,email,avatar',
            'creator:id,name,email,avatar',
            'section:id,name',
            'tags:id,name,color',
            'dependencies',
            'dependents',
            'customFieldValues:id,task_id,custom_field_id,value',
        ])->withCount('subtasks')->get();

        return response()->json([
            'data' => $tasks,
        ]);
    }

    /**
     * Create a new task.
     */
    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        $this->authorize('create', [Task::class, $project]);

        $validated = $request->validated();

        try {
            $task = $this->taskService->createTask($project, $validated, auth()->user());

            return response()->json([
                'data' => $task->load([
                    'assignee:id,name,email,avatar',
                    'creator:id,name,email,avatar',
                    'section:id,name',
                    'tags:id,name,color',
                ]),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Get a specific task.
     */
    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        return response()->json([
            'data' => $task->load([
                'assignee:id,name,email,avatar',
                'creator:id,name,email,avatar',
                'section:id,name',
                'tags:id,name,color',
                'subtasks',
                'dependencies' => fn($q) => $q->select('tasks.id','tasks.name','tasks.status','tasks.completed_at','tasks.due_date','tasks.start_date','tasks.assignee_id')->with('assignee:id,name,avatar'),
                'dependents' => fn($q) => $q->select('tasks.id','tasks.name','tasks.status','tasks.completed_at','tasks.due_date','tasks.start_date','tasks.assignee_id')->with('assignee:id,name,avatar'),
                'customFieldValues',
                'attachments',
            ]),
        ]);
    }

    /**
     * Update a task.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {

       
        $this->authorize('update', $task);

        $validated = $request->validated();

        try {
            $updatedTask = $this->taskService->updateTask($task, $validated);

            return response()->json([
                'data' => $updatedTask->load([
                    'assignee:id,name,email,avatar',
                    'creator:id,name,email,avatar',
                    'section:id,name',
                    'tags:id,name,color',
                ]),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Delete a task.
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $this->taskService->deleteTask($task);

        return response()->json(null, 204);
    }

    /**
     * Mark a task as complete.
     */
    public function complete(Task $task): JsonResponse
    {
        $this->authorize('complete', $task);

        try {
            $completedTask = $this->taskService->completeTask($task, auth()->user());

            return response()->json([
                'data' => $completedTask->load([
                    'assignee:id,name,email,avatar',
                    'creator:id,name,email,avatar',
                    'completedBy:id,name,email',
                ]),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Reopen a completed task.
     */
    public function reopen(Task $task): JsonResponse
    {
        $this->authorize('complete', $task);

        try {
            $reopenedTask = $this->taskService->reopenTask($task);

            return response()->json([
                'data' => $reopenedTask->load([
                    'assignee:id,name,email,avatar',
                    'creator:id,name,email,avatar',
                ]),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Duplicate a task.
     */
    public function duplicate(Task $task): JsonResponse
    {
        // For personal tasks (no project), check if user owns the task
        if ($task->project === null) {
            if ($task->creator_id !== auth()->id() && $task->assignee_id !== auth()->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        } else {
            $this->authorize('create', [Task::class, $task->project]);
        }

        try {
            $duplicatedTask = $this->taskService->duplicateTask($task);

            return response()->json([
                'data' => $duplicatedTask->load([
                    'assignee:id,name,email,avatar',
                    'creator:id,name,email,avatar',
                    'section:id,name',
                    'tags:id,name,color',
                ]),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Move a task to a different section (and optional position).
     */
    public function move(MoveTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validated();

        try {
            // Check if this is a My Tasks move (has my_tasks_section_id)
            if (isset($validated['my_tasks_section_id'])) {
                $myTasksSection = \App\Models\Section::findOrFail($validated['my_tasks_section_id']);
                
                // Verify it's a My Tasks section
                if (!$myTasksSection->is_my_tasks || $myTasksSection->user_id !== auth()->id()) {
                    return response()->json(['message' => 'Invalid My Tasks section'], 422);
                }
                
                $movedTask = $this->taskService->moveTaskInMyTasks(
                    $task, 
                    $myTasksSection, 
                    $validated['my_tasks_position'] ?? null
                );
                
                return response()->json([
                    'data' => $movedTask->load([
                        'assignee:id,name,email,avatar',
                        'section:id,name',
                        'myTasksSection:id,name',
                        'tags:id,name,color',
                    ]),
                ]);
            }
            
            // Otherwise, it's a project section move
            if (isset($validated['section_id'])) {
                $section = \App\Models\Section::findOrFail($validated['section_id']);
                $movedTask = $this->taskService->moveTask($task, $section, $validated['position'] ?? null);
            } else {
                // Same section, just reposition
                $movedTask = $this->taskService->repositionTask($task, $validated['position'] ?? null);
            }

            return response()->json([
                'data' => $movedTask->load([
                    'assignee:id,name,email,avatar',
                    'section:id,name',
                    'myTasksSection:id,name',
                    'tags:id,name,color',
                ]),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Add a dependency to a task.
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
                'data' => $task->load([
                    'dependencies' => fn($q) => $q->select('tasks.id','tasks.name','tasks.status','tasks.completed_at','tasks.due_date','tasks.start_date','tasks.assignee_id')->with('assignee:id,name,avatar'),
                    'dependents' => fn($q) => $q->select('tasks.id','tasks.name','tasks.status','tasks.completed_at','tasks.due_date','tasks.start_date','tasks.assignee_id')->with('assignee:id,name,avatar'),
                ]),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Remove a dependency from a task.
     */
    public function removeDependency(Task $task, Task $dependsOnTask): JsonResponse
    {
        $this->authorize('update', $task);

        $this->taskService->removeDependency($task, $dependsOnTask);

        return response()->json([
            'data' => $task->load([
                'dependencies' => fn($q) => $q->select('tasks.id','tasks.name','tasks.status','tasks.completed_at','tasks.due_date','tasks.start_date','tasks.assignee_id')->with('assignee:id,name,avatar'),
                'dependents' => fn($q) => $q->select('tasks.id','tasks.name','tasks.status','tasks.completed_at','tasks.due_date','tasks.start_date','tasks.assignee_id')->with('assignee:id,name,avatar'),
            ]),
        ]);
    }

    /**
     * Get task activities (paginated, descending).
     */
    public function activities(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $activities = $task->activities()
            ->with('user:id,name,email,avatar')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Manually format created_at as ISO 8601 UTC since $timestamps = false
        // bypasses Eloquent's normal date serialization pipeline
        $items = collect($activities->items())->map(function ($activity) {
            $data = $activity->toArray();
            $data['created_at'] = $activity->getRawOriginal('created_at')
                ? \Carbon\Carbon::parse($activity->getRawOriginal('created_at'), 'UTC')
                    ->toIso8601ZuluString()
                : null;
            return $data;
        });

        return response()->json([
            'data'      => $items,
            'has_more'  => $activities->hasMorePages(),
            'next_page' => $activities->hasMorePages() ? $activities->currentPage() + 1 : null,
            'total'     => $activities->total(),
        ]);
    }

    public function subtasks(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $subtasks = $task->subtasks()
            ->with('assignee:id,name,email,avatar')
            ->orderBy('position')
            ->orderBy('created_at')
            ->get();

        return response()->json(['data' => $subtasks]);
    }

    public function storeSubtask(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $request->validate(['name' => 'required|string|max:255']);

        try {
            $subtask = $this->taskService->createSubtask($task, $request->only([
                'name', 'assignee_id', 'start_date', 'due_date', 'status',
            ]), auth()->user());

            return response()->json([
                'data' => $subtask->load('assignee:id,name,email,avatar'),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Set a custom field value for a task.
     */
    public function setCustomFieldValue(Request $request, Task $task, CustomField $customField): JsonResponse
    {
        $this->authorize('update', $task);

        $request->validate([
            'value' => 'nullable',
        ]);

        try {
            // Check that the custom field belongs to the same project as the task
            if ($customField->project_id !== $task->project_id) {
                return response()->json(['error' => 'Custom field does not belong to this project'], 403);
            }

            // Use the service to set the field value with proper validation
            $this->customFieldService->setFieldValue($task, $customField, $request->input('value'));

            return response()->json([
                'message' => 'Custom field value updated successfully',
                'data' => [
                    'task_id' => $task->id,
                    'custom_field_id' => $customField->id,
                    'value' => $request->input('value'),
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update custom field value: ' . $e->getMessage()], 500);
        }
    }
}
