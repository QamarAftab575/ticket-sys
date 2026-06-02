<?php

namespace App\Services;

use App\Models\MyTaskViewPreference;
use App\Models\Task;
use App\Models\User;

class MyTasksService
{
    /**
     * Get user's tasks with filters, sort, and grouping.
     */
    public function getUserTasks(
        User $user,
        array $filters = [],
        array $sort = [],
        ?string $grouping = null,
        int $page = 1,
        int $perPage = 50
    ): array {
        // Get user's My Tasks section IDs
        $myTasksSectionIds = \App\Models\Section::myTasks($user->id)->pluck('id')->toArray();

        // Show tasks that are either:
        // 1. Assigned to the user (from any project or My Tasks)
        // 2. Created by the user in My Tasks sections (even if not assigned)
        $query = Task::where(function ($q) use ($user, $myTasksSectionIds) {
                // Condition 1: All tasks assigned to the user
                $q->where('assignee_id', $user->id)
                  // Condition 2: Tasks created by user in My Tasks sections
                  ->orWhere(function ($subQ) use ($user, $myTasksSectionIds) {
                      $subQ->where('creator_id', $user->id)
                           ->whereIn('my_tasks_section_id', $myTasksSectionIds)
                           ->whereNotNull('my_tasks_section_id');
                  });
            })
            ->select([
                'tasks.id',
                'tasks.name',
                'tasks.description',
                'tasks.status',
                'tasks.priority',
                'tasks.section_id',
                'tasks.my_tasks_section_id',
                'tasks.project_id',
                'tasks.assignee_id',
                'tasks.creator_id',
                'tasks.completed_by',
                'tasks.start_date',
                'tasks.due_date',
                'tasks.completed_at',
                'tasks.position',
                'tasks.my_tasks_position',
                'tasks.is_milestone',
                'tasks.created_at',
                'tasks.updated_at',
            ])
            ->with([
                'assignee:id,name,email,avatar',
                'creator:id,name,email,avatar',
                'completedBy:id,name,email,avatar',
                'project:id,name,color,icon',
                'project.members' => function ($query) {
                    $query->select('users.id', 'users.name', 'users.email', 'users.avatar');
                },
                'section:id,name',
                'myTasksSection:id,name',
            ])
            ->withCount('subtasks');

        $query = $this->applyFilters($query, $filters);
        $query = $this->applySortRules($query, $sort);

        // Default sort by My Tasks section and position if no sort specified
        // Tasks without my_tasks_section_id (project tasks) go to "Recently Assigned" section
        if (empty($sort)) {
            // Get the "Recently Assigned" section ID for this user (or first section)
            $recentlyAssignedSection = \App\Models\Section::myTasks($user->id)
                ->where(function($q) {
                    $q->where('name', 'Recently Assigned')
                      ->orWhere('position', 0);
                })
                ->orderBy('position')
                ->first();
            
            if ($recentlyAssignedSection) {
                $recentlyAssignedId = $recentlyAssignedSection->id;
                
                // Sort: NULL my_tasks_section_id treated as "Recently Assigned"
                $query->orderByRaw("COALESCE(my_tasks_section_id, '{$recentlyAssignedId}') ASC")
                      ->orderByRaw('COALESCE(my_tasks_position, 999999) ASC')
                      ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
                      ->orderBy('due_date', 'asc')
                      ->orderBy('created_at', 'desc');
            } else {
                // Fallback if no sections exist yet
                $query->orderByRaw('COALESCE(my_tasks_position, 999999) ASC')
                      ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
                      ->orderBy('due_date', 'asc')
                      ->orderBy('created_at', 'desc');
            }
        }

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        $items = $paginated->items();
        
        // Auto-assign My Tasks section for tasks that don't have one
        // First, ensure the user has My Tasks sections (create defaults if needed)
        $myTasksSections = \App\Models\Section::myTasks($user->id)->orderBy('position')->get();
        
        if ($myTasksSections->isEmpty()) {
            // Create default sections if they don't exist
            $sectionService = app(\App\Services\MyTasksSectionService::class);
            $myTasksSections = $sectionService->createDefaultSections($user);
        }
        
        // Get the "Recently Assigned" section (or first section as fallback)
        $recentlyAssignedSection = $myTasksSections->first();
        
        if ($recentlyAssignedSection) {
            foreach ($items as $task) {
                if (!$task->my_tasks_section_id && $task->assignee_id === $user->id) {
                    // Get max position in Recently Assigned section
                    $maxPosition = Task::where('my_tasks_section_id', $recentlyAssignedSection->id)
                        ->where('assignee_id', $user->id)
                        ->max('my_tasks_position');
                    
                    // Update task with My Tasks section
                    $task->update([
                        'my_tasks_section_id' => $recentlyAssignedSection->id,
                        'my_tasks_position' => $maxPosition !== null ? $maxPosition + 1 : 0,
                    ]);
                    
                    // Reload the relationship
                    $task->load('myTasksSection:id,name');
                }
            }
        }
        
        $grouped = $grouping ? $this->groupTasks($items, $grouping) : null;

        return [
            'tasks'        => $grouped ?? $items,
            'total'        => $paginated->total(),
            'pages'        => $paginated->lastPage(),
            'current_page' => $paginated->currentPage(),
            'per_page'     => $perPage,
        ];
    }

    /**
     * Apply filters to task query.
     */
    private function applyFilters($query, array $filters)
    {
        foreach ($filters as $filter) {
            $field = $filter['field'] ?? null;
            $operator = $filter['operator'] ?? '=';
            $value = $filter['value'] ?? null;

            if (!$field) {
                continue;
            }

            match ($operator) {
                'equals' => $query = $query->where($field, $value),
                'not_equals' => $query = $query->where($field, '!=', $value),
                'contains' => $query = $query->where($field, 'like', "%{$value}%"),
                'not_contains' => $query = $query->where($field, 'not like', "%{$value}%"),
                'greater_than' => $query = $query->where($field, '>', $value),
                'less_than' => $query = $query->where($field, '<', $value),
                'is_empty' => $query = $query->whereNull($field),
                'is_not_empty' => $query = $query->whereNotNull($field),
                default => null,
            };
        }

        return $query;
    }

    /**
     * Apply sort rules to task query.
     */
    private function applySortRules($query, array $sort)
    {
        foreach ($sort as $rule) {
            $field = $rule['field'] ?? null;
            $direction = $rule['direction'] ?? 'asc';

            if ($field) {
                // Handle special sorting for project name
                if ($field === 'project_name') {
                    $query->join('projects', 'tasks.project_id', '=', 'projects.id')
                          ->orderBy('projects.name', $direction)
                          ->select('tasks.*');
                } else {
                    $query->orderBy($field, $direction);
                }
            }
        }

        return $query;
    }

    /**
     * Group tasks by field.
     */
    private function groupTasks(array $tasks, string $grouping): array
    {
        $grouped = [];

        foreach ($tasks as $task) {
            $key = match ($grouping) {
                'project' => $task->project?->name ?? 'No Project',
                'status' => $this->formatStatus($task->status),
                'priority' => ucfirst($task->priority),
                'due_date' => $this->getDueDateGroup($task->due_date),
                'section' => $task->section?->name ?? 'No Section',
                default => $task->{$grouping} ?? 'Ungrouped',
            };

            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $task;
        }

        // Sort groups by priority for due_date grouping
        if ($grouping === 'due_date') {
            $order = ['Overdue', 'Today', 'Tomorrow', 'This Week', 'Next Week', 'Later', 'No Due Date'];
            $sortedGrouped = [];
            foreach ($order as $group) {
                if (isset($grouped[$group])) {
                    $sortedGrouped[$group] = $grouped[$group];
                }
            }
            return $sortedGrouped;
        }

        return $grouped;
    }

    /**
     * Format status for display.
     */
    private function formatStatus(string $status): string
    {
        return match ($status) {
            'to_do' => 'To Do',
            'in_progress' => 'In Progress',
            'blocked' => 'Blocked',
            'in_review' => 'In Review',
            'complete' => 'Complete',
            default => ucfirst($status),
        };
    }

    /**
     * Get due date group for task.
     */
    private function getDueDateGroup(?string $dueDate): string
    {
        if (!$dueDate) {
            return 'No Due Date';
        }

        $today = now()->startOfDay();
        $taskDueDate = \Carbon\Carbon::parse($dueDate)->startOfDay();

        if ($taskDueDate->lt($today)) {
            return 'Overdue';
        }

        if ($taskDueDate->eq($today)) {
            return 'Today';
        }

        if ($taskDueDate->eq($today->copy()->addDay())) {
            return 'Tomorrow';
        }

        if ($taskDueDate->lte($today->copy()->endOfWeek())) {
            return 'This Week';
        }

        if ($taskDueDate->lte($today->copy()->addWeek()->endOfWeek())) {
            return 'Next Week';
        }

        return 'Later';
    }

    /**
     * Save view preferences for My Tasks.
     */
    public function saveViewPreferences(User $user, array $data): MyTaskViewPreference
    {
        return MyTaskViewPreference::updateOrCreate(
            [
                'user_id'   => $user->id,
                'view_type' => $data['view_type'],
            ],
            [
                'sort'               => isset($data['sort']) ? json_decode($data['sort'], true) : null,
                'grouping'           => $data['grouping'] ?? null,
                'section_order'      => isset($data['section_order']) ? json_decode($data['section_order'], true) : null,
                'collapsed_sections' => isset($data['collapsed_sections']) ? json_decode($data['collapsed_sections'], true) : null,
                'filters'            => isset($data['filters']) ? json_decode($data['filters'], true) : null,
            ]
        );
    }

    /**
     * Get view preferences for My Tasks.
     */
    public function getViewPreferences(User $user, string $viewType): ?MyTaskViewPreference
    {
        return MyTaskViewPreference::where([
            'user_id'   => $user->id,
            'view_type' => $viewType,
        ])->first();
    }

    /**
     * Create a task for My Tasks.
     */
    public function createTask(User $user, array $data): Task
    {
        // Get the target My Tasks section
        $myTasksSectionId = $data['section_id'] ?? null;
        $myTasksPosition = 0;

        if ($myTasksSectionId) {
            // Get the highest position in the My Tasks section (zero-based indexing)
            $maxPosition = Task::where('my_tasks_section_id', $myTasksSectionId)
                ->where('assignee_id', $user->id)
                ->max('my_tasks_position');
            $myTasksPosition = $maxPosition !== null ? $maxPosition + 1 : 0;
        } else {
            // If no section given, assign to first My Tasks section
            $firstSection = \App\Models\Section::myTasks($user->id)->orderBy('position')->first();
            $myTasksSectionId = $firstSection?->id;
            
            if ($myTasksSectionId) {
                $maxPosition = Task::where('my_tasks_section_id', $myTasksSectionId)
                    ->where('assignee_id', $user->id)
                    ->max('my_tasks_position');
                $myTasksPosition = $maxPosition !== null ? $maxPosition + 1 : 0;
            }
        }

        $task = Task::create([
            'name'                 => $data['name'],
            'status'               => $data['status'] ?? 'to_do',
            'priority'             => $data['priority'] ?? null,
            'due_date'             => $data['due_date'] ?? null,
            'description'          => $data['description'] ?? null,
            'section_id'           => null,  // No project section initially
            'my_tasks_section_id'  => $myTasksSectionId,
            'assignee_id'          => $user->id,
            'creator_id'           => $user->id,
            'project_id'           => $data['project_id'] ?? null,
            'position'             => 0,  // Default position (zero-based indexing)
            'my_tasks_position'    => $myTasksPosition,
        ]);

        return $task->load([
            'assignee:id,name,email,avatar',
            'creator:id,name,email,avatar',
            'section:id,name',
            'myTasksSection:id,name',
            'project:id,name,color,icon',
        ]);
    }
}