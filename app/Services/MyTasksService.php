<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\ViewPreference;

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
        $query = Task::where('assignee_id', $user->id)
            ->with([
                'assignee:id,name,email,avatar',
                'creator:id,name,email,avatar',
                'completedBy:id,name,email,avatar',
                'project:id,name,color,icon',
                'section:id,name',
                'dependencies:id,name,status',
                'dependents:id,name,status',
                'customFieldValues:id,task_id,custom_field_id,value',
            ])
            ->withCount('subtasks');

        $query = $this->applyFilters($query, $filters);
        $query = $this->applySortRules($query, $sort);

        // Default sort by due date if no sort specified
        if (empty($sort)) {
            $query->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
                  ->orderBy('due_date', 'asc')
                  ->orderBy('created_at', 'desc');
        }

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        $items = $paginated->items();
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
    public function saveViewPreferences(User $user, array $data): ViewPreference
    {
        $preference = ViewPreference::updateOrCreate(
            [
                'user_id' => $user->id,
                'project_id' => null, // My Tasks is not project-specific
                'view_type' => $data['view_type'],
                'context' => 'my_tasks', // Add context to differentiate from project views
            ],
            [
                'filters' => $data['filters'] ?? null,
                'sort' => $data['sort'] ?? null,
                'grouping' => $data['grouping'] ?? null,
                'column_widths' => $data['column_widths'] ?? null,
                'hidden_columns' => $data['hidden_columns'] ?? null,
                'collapsed_sections' => $data['collapsed_sections'] ?? null,
                'card_fields' => $data['card_fields'] ?? null,
                'zoom_level' => $data['zoom_level'] ?? null,
            ]
        );

        return $preference;
    }

    /**
     * Get view preferences for My Tasks.
     */
    public function getViewPreferences(User $user, string $viewType): ?ViewPreference
    {
        return ViewPreference::where([
            'user_id' => $user->id,
            'project_id' => null,
            'view_type' => $viewType,
            'context' => 'my_tasks',
        ])->first();
    }
}