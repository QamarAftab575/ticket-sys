<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskSortPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class TaskSortService
{
    /**
     * Valid sortable fields for tasks.
     */
    private const VALID_SORT_FIELDS = [
        'name',
        'assignee_id',
        'due_date',
        'priority',
        'status',
        'start_date',
        'created_at',
        'updated_at',
        'position',
        'is_milestone',
    ];

    /**
     * Valid sort directions.
     */
    private const VALID_DIRECTIONS = ['asc', 'desc'];

    /**
     * Maximum number of sort criteria allowed.
     */
    private const MAX_SORT_CRITERIA = 3;

    /**
     * Sort tasks by the given criteria.
     *
     * @param Builder $query
     * @param array $sortCriteria Array of sort criteria with 'field' and 'direction' keys
     * @return Builder
     */
    public function applySortCriteria(Builder|Relation $query, array $sortCriteria): Builder|Relation
    {
        if (empty($sortCriteria)) {
            return $query;
        }

        // Limit to maximum sort criteria
        $sortCriteria = array_slice($sortCriteria, 0, self::MAX_SORT_CRITERIA);

        foreach ($sortCriteria as $criteria) {
            $field = $criteria['field'] ?? null;
            $direction = $criteria['direction'] ?? 'asc';

            if (!$this->isValidSortField($field)) {
                continue;
            }

            if (!$this->isValidDirection($direction)) {
                $direction = 'asc';
            }

            // Handle custom field sorting
            if (str_starts_with($field, 'custom_field_')) {
                $query = $this->sortByCustomField($query, $field, $direction);
            } else {
                $query->orderBy($field, $direction);
            }
        }

        return $query;
    }

    /**
     * Sort tasks by a custom field.
     *
     * @param Builder $query
     * @param string $field Custom field identifier (e.g., 'custom_field_123')
     * @param string $direction Sort direction (asc or desc)
     * @return Builder
     */
    private function sortByCustomField(Builder $query, string $field, string $direction): Builder
    {
        $fieldId = str_replace('custom_field_', '', $field);

        return $query->leftJoin('custom_field_values', function ($join) use ($fieldId) {
            $join->on('tasks.id', '=', 'custom_field_values.task_id')
                ->where('custom_field_values.custom_field_id', '=', $fieldId);
        })
            ->orderBy('custom_field_values.value', $direction)
            ->select('tasks.*');
    }

    /**
     * Check if a field is valid for sorting.
     *
     * @param string $field
     * @return bool
     */
    public function isValidSortField(string $field): bool
    {
        // Check if it's a standard field
        if (in_array($field, self::VALID_SORT_FIELDS)) {
            return true;
        }

        // Check if it's a custom field
        if (str_starts_with($field, 'custom_field_')) {
            return true;
        }

        return false;
    }

    /**
     * Check if a direction is valid.
     *
     * @param string $direction
     * @return bool
     */
    public function isValidDirection(string $direction): bool
    {
        return in_array(strtolower($direction), self::VALID_DIRECTIONS);
    }

    /**
     * Save sort preference for a user in a project.
     *
     * @param User $user
     * @param Project $project
     * @param array $sortCriteria Array of sort criteria
     * @return TaskSortPreference
     */
    public function saveSortPreference(User $user, Project $project, array $sortCriteria): TaskSortPreference
    {
        // Validate sort criteria
        $validatedCriteria = $this->validateAndNormalizeSortCriteria($sortCriteria);

        // Find or create preference
        $preference = TaskSortPreference::firstOrNew([
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        $preference->sort_criteria = $validatedCriteria;
        $preference->save();

        return $preference;
    }

    /**
     * Get sort preference for a user in a project.
     *
     * @param User $user
     * @param Project $project
     * @return array|null
     */
    public function getSortPreference(User $user, Project $project): ?array
    {
        $preference = TaskSortPreference::where('user_id', $user->id)
            ->where('project_id', $project->id)
            ->first();

        return $preference?->sort_criteria;
    }

    /**
     * Delete sort preference for a user in a project.
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function deleteSortPreference(User $user, Project $project): bool
    {
        return TaskSortPreference::where('user_id', $user->id)
            ->where('project_id', $project->id)
            ->delete() > 0;
    }

    /**
     * Validate and normalize sort criteria.
     *
     * @param array $sortCriteria
     * @return array
     */
    public function validateAndNormalizeSortCriteria(array $sortCriteria): array
    {
        $validated = [];

        // Limit to maximum sort criteria
        $sortCriteria = array_slice($sortCriteria, 0, self::MAX_SORT_CRITERIA);

        foreach ($sortCriteria as $criteria) {
            $field = $criteria['field'] ?? null;
            $direction = strtolower($criteria['direction'] ?? 'asc');

            // Skip invalid fields
            if (!$this->isValidSortField($field)) {
                continue;
            }

            // Normalize direction
            if (!$this->isValidDirection($direction)) {
                $direction = 'asc';
            }

            $validated[] = [
                'field' => $field,
                'direction' => $direction,
            ];
        }

        return $validated;
    }

    /**
     * Get default sort criteria.
     *
     * @return array
     */
    public function getDefaultSortCriteria(): array
    {
        return [
            [
                'field' => 'due_date',
                'direction' => 'asc',
            ],
        ];
    }

    /**
     * Get available sort options.
     *
     * @return array
     */
    public function getAvailableSortOptions(): array
    {
        return [
            'name' => 'Task Name',
            'assignee_id' => 'Assignee',
            'due_date' => 'Due Date',
            'priority' => 'Priority',
            'status' => 'Status',
            'start_date' => 'Start Date',
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date',
            'position' => 'Position',
            'is_milestone' => 'Milestone',
        ];
    }
}
