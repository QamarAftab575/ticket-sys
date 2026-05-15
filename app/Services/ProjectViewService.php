<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\ViewPreference;
use Illuminate\Pagination\Paginator;

class ProjectViewService
{
    /**
     * Get tasks with filters, sort, and grouping.
     */
    public function getTasks(
        Project $project,
        array $filters = [],
        array $sort = [],
        ?string $grouping = null,
        int $page = 1,
        int $perPage = 50
    ): array {
        $query = $project->tasks()->with([
            'assignee:id,name,email,avatar',
            'creator:id,name,email,avatar',
            'completedBy:id,name,email,avatar',
            'section:id,name',
            'dependencies:id,name,status',
            'dependents:id,name,status',
            'customFieldValues:id,task_id,custom_field_id,value',
        ])->withCount('subtasks');

        $query = $this->applyFilters($query, $filters);
        $query = $this->applySortRules($query, $sort);

        // paginate() runs count + data in 2 queries — avoid extra manual count()
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
                $query->orderBy($field, $direction);
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
            $key = $task->{$grouping} ?? 'Ungrouped';
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $task;
        }

        return $grouped;
    }

    /**
     * Get project files.
     */
    public function getProjectFiles(
        Project $project,
        ?string $search = null,
        ?string $type = null,
        ?string $uploadedBy = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $page = 1,
        int $perPage = 50
    ): array {
        $query = $project->attachments();

        // Apply search
        if ($search) {
            $query->where('filename', 'like', "%{$search}%");
        }

        // Apply type filter
        if ($type) {
            $query->where('mime_type', 'like', "{$type}%");
        }

        // Apply uploaded by filter
        if ($uploadedBy) {
            $query->where('user_id', $uploadedBy);
        }

        // Apply date range filter
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $total = $query->count();
        $files = $query->with(['user', 'task'])->paginate($perPage, ['*'], 'page', $page);

        return [
            'files' => $files->items(),
            'total' => $total,
            'pages' => $files->lastPage(),
            'current_page' => $files->currentPage(),
            'per_page' => $perPage,
        ];
    }

    /**
     * Get dashboard data.
     */
    public function getDashboardData(Project $project): array
    {
        $stats = $this->getProjectStats($project);

        return [
            'stats' => $stats,
            'widgets' => [
                'project_status',
                'task_completion',
                'tasks_by_assignee',
                'tasks_by_priority',
                'upcoming_milestones',
                'overdue_tasks',
                'recent_activity',
            ],
        ];
    }

    /**
     * Get project statistics.
     */
    private function getProjectStats(Project $project): array
    {
        $total = $project->tasks()->count();
        $completed = $project->tasks()->where('status', 'complete')->count();
        $overdue = $project->tasks()
            ->where('due_date', '<', now()->toDateString())
            ->where('status', '!=', 'complete')
            ->count();

        $byPriority = $project->tasks()
            ->selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // Get assignee stats with user details
        $assigneeStats = $project->tasks()
            ->selectRaw('assignee_id, count(*) as total, sum(case when status = "complete" then 1 else 0 end) as completed')
            ->groupBy('assignee_id')
            ->with('assignee:id,name,avatar')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->assignee_id,
                    'name' => $task->assignee?->name ?? 'Unassigned',
                    'avatar' => $task->assignee?->avatar,
                    'total' => $task->total,
                    'completed' => $task->completed ?? 0,
                ];
            })
            ->values()
            ->toArray();

        return [
            'total_tasks'            => $total,
            'completed_tasks'        => $completed,
            'completion_percentage'  => $total > 0 ? round(($completed / $total) * 100) : 0,
            'overdue_count'          => $overdue,
            'tasks_by_priority'      => $byPriority,
            'tasks_by_assignee'      => $assigneeStats,
        ];
    }

    /**
     * Save view preferences.
     */
    public function saveViewPreferences(Project $project, User $user, array $data): ViewPreference
    {
        $preference = ViewPreference::updateOrCreate(
            [
                'user_id' => $user->id,
                'project_id' => $project->id,
                'view_type' => $data['view_type'],
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
     * Get view preferences.
     */
    public function getViewPreferences(Project $project, User $user, string $viewType): ?ViewPreference
    {
        return ViewPreference::where([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'view_type' => $viewType,
        ])->first();
    }
}
