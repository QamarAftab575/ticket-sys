<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskGroupPreference;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TaskGroupService
{
    /**
     * Valid groupable fields for tasks.
     */
    private const VALID_GROUP_FIELDS = [
        'assignee',
        'due_date',
        'priority',
        'status',
        'section',
        'project',
    ];

    /**
     * Group tasks by the given field.
     *
     * @param Collection $tasks
     * @param string $groupBy Field to group by
     * @return array Array of groups with headers and task counts
     */
    public function groupTasks(Collection $tasks, string $groupBy): array
    {
        if (!$this->isValidGroupField($groupBy)) {
            return [];
        }

        // Handle custom field grouping
        if (str_starts_with($groupBy, 'custom_field_')) {
            return $this->groupByCustomField($tasks, $groupBy);
        }

        return match ($groupBy) {
            'assignee' => $this->groupByAssignee($tasks),
            'due_date' => $this->groupByDueDate($tasks),
            'priority' => $this->groupByPriority($tasks),
            'status' => $this->groupByStatus($tasks),
            'section' => $this->groupBySection($tasks),
            'project' => $this->groupByProject($tasks),
            default => [],
        };
    }

    /**
     * Group tasks by assignee.
     *
     * @param Collection $tasks
     * @return array
     */
    private function groupByAssignee(Collection $tasks): array
    {
        $groups = [];

        // Group unassigned tasks
        $unassigned = $tasks->filter(fn($task) => $task->assignee_id === null);
        if ($unassigned->isNotEmpty()) {
            $groups['unassigned'] = [
                'header' => 'Unassigned',
                'count' => $unassigned->count(),
                'tasks' => $unassigned->values()->all(),
            ];
        }

        // Group by assignee
        $byAssignee = $tasks->filter(fn($task) => $task->assignee_id !== null)
            ->groupBy(fn($task) => $task->assignee_id);

        foreach ($byAssignee as $assigneeId => $assigneeTasks) {
            $assignee = $assigneeTasks->first()->assignee;
            $groups[$assigneeId] = [
                'header' => $assignee?->name ?? 'Unknown',
                'count' => $assigneeTasks->count(),
                'tasks' => $assigneeTasks->values()->all(),
            ];
        }

        return $groups;
    }

    /**
     * Group tasks by due date with predefined groups.
     *
     * @param Collection $tasks
     * @return array
     */
    private function groupByDueDate(Collection $tasks): array
    {
        $groups = [];
        $today = Carbon::today();

        // Overdue
        $overdue = $tasks->filter(function ($task) use ($today) {
            return $task->due_date && $task->due_date < $today && $task->status !== 'complete';
        });
        if ($overdue->isNotEmpty()) {
            $groups['overdue'] = [
                'header' => 'Overdue',
                'count' => $overdue->count(),
                'tasks' => $overdue->values()->all(),
            ];
        }

        // Today
        $todayTasks = $tasks->filter(fn($task) => $task->due_date && $task->due_date->isToday());
        if ($todayTasks->isNotEmpty()) {
            $groups['today'] = [
                'header' => 'Today',
                'count' => $todayTasks->count(),
                'tasks' => $todayTasks->values()->all(),
            ];
        }

        // This week
        $thisWeek = $tasks->filter(function ($task) use ($today) {
            return $task->due_date
                && $task->due_date > $today
                && $task->due_date <= $today->copy()->endOfWeek();
        });
        if ($thisWeek->isNotEmpty()) {
            $groups['this_week'] = [
                'header' => 'This Week',
                'count' => $thisWeek->count(),
                'tasks' => $thisWeek->values()->all(),
            ];
        }

        // This month
        $thisMonth = $tasks->filter(function ($task) use ($today) {
            return $task->due_date
                && $task->due_date > $today->copy()->endOfWeek()
                && $task->due_date <= $today->copy()->endOfMonth();
        });
        if ($thisMonth->isNotEmpty()) {
            $groups['this_month'] = [
                'header' => 'This Month',
                'count' => $thisMonth->count(),
                'tasks' => $thisMonth->values()->all(),
            ];
        }

        // Later
        $later = $tasks->filter(function ($task) use ($today) {
            return $task->due_date && $task->due_date > $today->copy()->endOfMonth();
        });
        if ($later->isNotEmpty()) {
            $groups['later'] = [
                'header' => 'Later',
                'count' => $later->count(),
                'tasks' => $later->values()->all(),
            ];
        }

        // No due date
        $noDueDate = $tasks->filter(fn($task) => $task->due_date === null);
        if ($noDueDate->isNotEmpty()) {
            $groups['no_due_date'] = [
                'header' => 'No Due Date',
                'count' => $noDueDate->count(),
                'tasks' => $noDueDate->values()->all(),
            ];
        }

        return $groups;
    }

    /**
     * Group tasks by priority.
     *
     * @param Collection $tasks
     * @return array
     */
    private function groupByPriority(Collection $tasks): array
    {
        $groups = [];
        $priorityOrder = ['urgent', 'high', 'medium', 'low'];

        foreach ($priorityOrder as $priority) {
            $priorityTasks = $tasks->filter(fn($task) => $task->priority === $priority);
            if ($priorityTasks->isNotEmpty()) {
                $groups[$priority] = [
                    'header' => ucfirst($priority),
                    'count' => $priorityTasks->count(),
                    'tasks' => $priorityTasks->values()->all(),
                ];
            }
        }

        return $groups;
    }

    /**
     * Group tasks by status.
     *
     * @param Collection $tasks
     * @return array
     */
    private function groupByStatus(Collection $tasks): array
    {
        $groups = [];
        $statusOrder = ['to_do', 'in_progress', 'blocked', 'in_review', 'complete'];

        foreach ($statusOrder as $status) {
            $statusTasks = $tasks->filter(fn($task) => $task->status === $status);
            if ($statusTasks->isNotEmpty()) {
                $groups[$status] = [
                    'header' => $this->formatStatusHeader($status),
                    'count' => $statusTasks->count(),
                    'tasks' => $statusTasks->values()->all(),
                ];
            }
        }

        return $groups;
    }

    /**
     * Group tasks by section.
     *
     * @param Collection $tasks
     * @return array
     */
    private function groupBySection(Collection $tasks): array
    {
        $groups = [];

        // Group tasks without section
        $noSection = $tasks->filter(fn($task) => $task->section_id === null);
        if ($noSection->isNotEmpty()) {
            $groups['no_section'] = [
                'header' => 'No Section',
                'count' => $noSection->count(),
                'tasks' => $noSection->values()->all(),
            ];
        }

        // Group by section
        $bySection = $tasks->filter(fn($task) => $task->section_id !== null)
            ->groupBy(fn($task) => $task->section_id);

        foreach ($bySection as $sectionId => $sectionTasks) {
            $section = $sectionTasks->first()->section;
            $groups[$sectionId] = [
                'header' => $section?->name ?? 'Unknown Section',
                'count' => $sectionTasks->count(),
                'tasks' => $sectionTasks->values()->all(),
            ];
        }

        return $groups;
    }

    /**
     * Group tasks by project.
     *
     * @param Collection $tasks
     * @return array
     */
    private function groupByProject(Collection $tasks): array
    {
        $groups = [];

        $byProject = $tasks->groupBy(fn($task) => $task->project_id);

        foreach ($byProject as $projectId => $projectTasks) {
            $project = $projectTasks->first()->project;
            $groups[$projectId] = [
                'header' => $project?->name ?? 'Unknown Project',
                'count' => $projectTasks->count(),
                'tasks' => $projectTasks->values()->all(),
            ];
        }

        return $groups;
    }

    /**
     * Group tasks by a custom field.
     *
     * @param Collection $tasks
     * @param string $field Custom field identifier (e.g., 'custom_field_123')
     * @return array
     */
    private function groupByCustomField(Collection $tasks, string $field): array
    {
        $groups = [];
        $fieldId = str_replace('custom_field_', '', $field);

        // Load custom field values for all tasks
        $tasks->load(['customFieldValues' => function ($query) use ($fieldId) {
            $query->where('custom_field_id', $fieldId);
        }]);

        // Group by custom field value
        $byValue = $tasks->groupBy(function ($task) use ($fieldId) {
            $value = $task->customFieldValues
                ->where('custom_field_id', $fieldId)
                ->first()?->value;
            return $value ?? 'no_value';
        });

        foreach ($byValue as $value => $valueTasks) {
            $header = $value === 'no_value' ? 'No Value' : $value;
            $groups[$value] = [
                'header' => $header,
                'count' => $valueTasks->count(),
                'tasks' => $valueTasks->values()->all(),
            ];
        }

        return $groups;
    }

    /**
     * Format status header for display.
     *
     * @param string $status
     * @return string
     */
    private function formatStatusHeader(string $status): string
    {
        return match ($status) {
            'to_do' => 'To Do',
            'in_progress' => 'In Progress',
            'in_review' => 'In Review',
            'complete' => 'Complete',
            'blocked' => 'Blocked',
            default => ucfirst($status),
        };
    }

    /**
     * Check if a field is valid for grouping.
     *
     * @param string $field
     * @return bool
     */
    public function isValidGroupField(string $field): bool
    {
        // Check if it's a standard field
        if (in_array($field, self::VALID_GROUP_FIELDS)) {
            return true;
        }

        // Check if it's a custom field
        if (str_starts_with($field, 'custom_field_')) {
            return true;
        }

        return false;
    }

    /**
     * Save group preference for a user in a project.
     *
     * @param User $user
     * @param Project $project
     * @param string $groupBy Field to group by
     * @param array $collapsedGroups Array of collapsed group identifiers
     * @return TaskGroupPreference
     */
    public function saveGroupPreference(
        User $user,
        Project $project,
        string $groupBy,
        array $collapsedGroups = []
    ): TaskGroupPreference {
        // Validate group field
        if (!$this->isValidGroupField($groupBy)) {
            throw new \InvalidArgumentException("Invalid group field: {$groupBy}");
        }

        // Find or create preference
        $preference = TaskGroupPreference::firstOrNew([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $preference->group_by = $groupBy;
        $preference->collapsed_groups = $collapsedGroups;
        $preference->save();

        return $preference;
    }

    /**
     * Get group preference for a user in a project.
     *
     * @param User $user
     * @param Project $project
     * @return array|null
     */
    public function getGroupPreference(User $user, Project $project): ?array
    {
        $preference = TaskGroupPreference::where('user_id', $user->id)
            ->where('project_id', $project->id)
            ->first();

        if (!$preference) {
            return null;
        }

        return [
            'group_by' => $preference->group_by,
            'collapsed_groups' => $preference->collapsed_groups ?? [],
        ];
    }

    /**
     * Delete group preference for a user in a project.
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function deleteGroupPreference(User $user, Project $project): bool
    {
        return TaskGroupPreference::where('user_id', $user->id)
            ->where('project_id', $project->id)
            ->delete() > 0;
    }

    /**
     * Toggle group collapse state.
     *
     * @param User $user
     * @param Project $project
     * @param string $groupId Group identifier to toggle
     * @return TaskGroupPreference
     */
    public function toggleGroupCollapse(User $user, Project $project, string $groupId): TaskGroupPreference
    {
        $preference = TaskGroupPreference::firstOrNew([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $collapsedGroups = $preference->collapsed_groups ?? [];

        if (in_array($groupId, $collapsedGroups)) {
            // Expand group
            $collapsedGroups = array_filter($collapsedGroups, fn($id) => $id !== $groupId);
        } else {
            // Collapse group
            $collapsedGroups[] = $groupId;
        }

        $preference->collapsed_groups = array_values($collapsedGroups);
        $preference->save();

        return $preference;
    }

    /**
     * Get available group options.
     *
     * @return array
     */
    public function getAvailableGroupOptions(): array
    {
        return [
            'assignee' => 'Assignee',
            'due_date' => 'Due Date',
            'priority' => 'Priority',
            'status' => 'Status',
            'section' => 'Section',
            'project' => 'Project',
        ];
    }
}
