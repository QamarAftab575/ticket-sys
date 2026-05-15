<?php

namespace App\Services;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class TaskFilterService
{
    /**
     * Filter tasks by assignee IDs (multi-select).
     *
     * @param Builder $query
     * @param array $assigneeIds
     * @return Builder
     */
    public function filterByAssignee(Builder $query, array $assigneeIds): Builder
    {
        if (empty($assigneeIds)) {
            return $query;
        }

        return $query->whereIn('assignee_id', $assigneeIds);
    }

    /**
     * Filter tasks by due date with predefined options.
     * Options: 'overdue', 'today', 'this_week', 'this_month', 'no_due_date'
     *
     * @param Builder $query
     * @param string $option
     * @return Builder
     */
    public function filterByDueDate(Builder $query, string $option): Builder
    {
        $today = Carbon::today();

        return match ($option) {
            'overdue' => $query->where('due_date', '<', $today)
                ->where('status', '!=', 'complete'),
            'today' => $query->whereDate('due_date', $today),
            'this_week' => $query->whereBetween('due_date', [
                $today->copy()->startOfWeek(),
                $today->copy()->endOfWeek(),
            ]),
            'this_month' => $query->whereBetween('due_date', [
                $today->copy()->startOfMonth(),
                $today->copy()->endOfMonth(),
            ]),
            'no_due_date' => $query->whereNull('due_date'),
            default => $query,
        };
    }

    /**
     * Filter tasks by priority (multi-select).
     *
     * @param Builder $query
     * @param array $priorities
     * @return Builder
     */
    public function filterByPriority(Builder $query, array $priorities): Builder
    {
        if (empty($priorities)) {
            return $query;
        }

        return $query->whereIn('priority', $priorities);
    }

    /**
     * Filter tasks by status (multi-select).
     *
     * @param Builder $query
     * @param array $statuses
     * @return Builder
     */
    public function filterByStatus(Builder $query, array $statuses): Builder
    {
        if (empty($statuses)) {
            return $query;
        }

        return $query->whereIn('status', $statuses);
    }

    /**
     * Filter tasks by completion status.
     * Options: 'complete', 'incomplete', 'all'
     *
     * @param Builder $query
     * @param string $option
     * @return Builder
     */
    public function filterByCompletion(Builder $query, string $option): Builder
    {
        return match ($option) {
            'complete' => $query->where('status', 'complete'),
            'incomplete' => $query->where('status', '!=', 'complete'),
            'all' => $query,
            default => $query,
        };
    }

    /**
     * Filter tasks by tag IDs (multi-select).
     *
     * @param Builder $query
     * @param array $tagIds
     * @return Builder
     */
    public function filterByTags(Builder $query, array $tagIds): Builder
    {
        if (empty($tagIds)) {
            return $query;
        }

        return $query->whereHas('tags', function ($q) use ($tagIds) {
            $q->whereIn('tag_id', $tagIds);
        });
    }

    /**
     * Filter tasks by custom field values.
     * Supports filtering by custom field ID and value.
     *
     * @param Builder $query
     * @param array $customFieldFilters Array of ['field_id' => 'value'] pairs
     * @return Builder
     */
    public function filterByCustomFields(Builder $query, array $customFieldFilters): Builder
    {
        if (empty($customFieldFilters)) {
            return $query;
        }

        foreach ($customFieldFilters as $fieldId => $value) {
            $query->whereHas('customFieldValues', function ($q) use ($fieldId, $value) {
                $q->where('custom_field_id', $fieldId)
                    ->where('value', $value);
            });
        }

        return $query;
    }

    /**
     * Filter tasks by project ID (for My Tasks page).
     *
     * @param Builder $query
     * @param array $projectIds
     * @return Builder
     */
    public function filterByProject(Builder $query, array $projectIds): Builder
    {
        if (empty($projectIds)) {
            return $query;
        }

        return $query->whereIn('project_id', $projectIds);
    }

    /**
     * Apply multiple filters at once.
     * Accepts an array of filter criteria.
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    public function applyFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['assignees']) && !empty($filters['assignees'])) {
            $query = $this->filterByAssignee($query, $filters['assignees']);
        }

        if (isset($filters['due_date']) && !empty($filters['due_date'])) {
            $query = $this->filterByDueDate($query, $filters['due_date']);
        }

        if (isset($filters['priorities']) && !empty($filters['priorities'])) {
            $query = $this->filterByPriority($query, $filters['priorities']);
        }

        if (isset($filters['statuses']) && !empty($filters['statuses'])) {
            $query = $this->filterByStatus($query, $filters['statuses']);
        }

        if (isset($filters['completion']) && !empty($filters['completion'])) {
            $query = $this->filterByCompletion($query, $filters['completion']);
        }

        if (isset($filters['tags']) && !empty($filters['tags'])) {
            $query = $this->filterByTags($query, $filters['tags']);
        }

        if (isset($filters['custom_fields']) && !empty($filters['custom_fields'])) {
            $query = $this->filterByCustomFields($query, $filters['custom_fields']);
        }

        if (isset($filters['projects']) && !empty($filters['projects'])) {
            $query = $this->filterByProject($query, $filters['projects']);
        }

        return $query;
    }

    /**
     * Count active filters.
     * Returns the number of active filter criteria.
     *
     * @param array $filters
     * @return int
     */
    public function countActiveFilters(array $filters): int
    {
        $count = 0;

        if (isset($filters['assignees']) && !empty($filters['assignees'])) {
            $count++;
        }

        if (isset($filters['due_date']) && !empty($filters['due_date'])) {
            $count++;
        }

        if (isset($filters['priorities']) && !empty($filters['priorities'])) {
            $count++;
        }

        if (isset($filters['statuses']) && !empty($filters['statuses'])) {
            $count++;
        }

        if (isset($filters['completion']) && !empty($filters['completion']) && $filters['completion'] !== 'all') {
            $count++;
        }

        if (isset($filters['tags']) && !empty($filters['tags'])) {
            $count++;
        }

        if (isset($filters['custom_fields']) && !empty($filters['custom_fields'])) {
            $count++;
        }

        if (isset($filters['projects']) && !empty($filters['projects'])) {
            $count++;
        }

        return $count;
    }
}
