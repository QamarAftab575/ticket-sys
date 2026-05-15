<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'project_id',
        'section_id',
        'parent_task_id',
        'name',
        'description',
        'assignee_id',
        'creator_id',
        'status',
        'priority',
        'visibility',
        'start_date',
        'due_date',
        'completed_at',
        'completed_by',
        'is_milestone',
        'position',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'due_date' => 'date:Y-m-d',
        'completed_at' => 'datetime',
        'is_milestone' => 'boolean',
        'position' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the project that owns the task.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the section that owns the task.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the user assigned to the task.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Get the user who created the task.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the user who completed the task.
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Get the parent task.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    /**
     * Get the subtasks of this task.
     */
    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    /**
     * Tasks this task is blocked by (must complete before this one can start).
     */
    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_task_id')
            ->withPivot('dependency_type')
            ->wherePivot('dependency_type', 'blocked_by');
    }

    /**
     * Tasks this task is blocking (waiting on this task to complete).
     */
    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_task_id')
            ->withPivot('dependency_type')
            ->wherePivot('dependency_type', 'blocks');
    }

    /**
     * Get the custom field values for this task.
     */
    public function customFieldValues(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    /**
     * Get the comments for this task.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the attachments for this task.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * Get the tags for this task.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'task_tag')
            ->withPivot('created_at');
    }

    /**
     * Get the activities for this task.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(TaskActivity::class);
    }

    /**
     * Scope to filter tasks by tag IDs.
     * Supports filtering by one or more tags.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $tagIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByTags($query, array $tagIds)
    {
        if (empty($tagIds)) {
            return $query;
        }

        return $query->whereHas('tags', function ($q) use ($tagIds) {
            $q->whereIn('tag_id', $tagIds);
        });
    }

    /**
     * Scope to filter tasks by assignee IDs (multi-select).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $assigneeIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByAssignee($query, array $assigneeIds)
    {
        if (empty($assigneeIds)) {
            return $query;
        }

        return $query->whereIn('assignee_id', $assigneeIds);
    }

    /**
     * Scope to filter tasks by priority (multi-select).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $priorities
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByPriority($query, array $priorities)
    {
        if (empty($priorities)) {
            return $query;
        }

        return $query->whereIn('priority', $priorities);
    }

    /**
     * Scope to filter tasks by status (multi-select).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $statuses
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByStatus($query, array $statuses)
    {
        if (empty($statuses)) {
            return $query;
        }

        return $query->whereIn('status', $statuses);
    }

    /**
     * Scope to filter tasks by completion status.
     * Options: 'complete', 'incomplete', 'all'
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $option
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByCompletion($query, string $option)
    {
        return match ($option) {
            'complete' => $query->where('status', 'complete'),
            'incomplete' => $query->where('status', '!=', 'complete'),
            'all' => $query,
            default => $query,
        };
    }

    /**
     * Scope to filter tasks by custom field values.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $customFieldFilters Array of ['field_id' => 'value'] pairs
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByCustomFields($query, array $customFieldFilters)
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
     * Scope to filter tasks by project ID (for My Tasks page).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $projectIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByProject($query, array $projectIds)
    {
        if (empty($projectIds)) {
            return $query;
        }

        return $query->whereIn('project_id', $projectIds);
    }

    /**
     * Scope to filter tasks by due date with predefined options.
     * Options: 'overdue', 'today', 'this_week', 'this_month', 'no_due_date'
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $option
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByDueDate($query, string $option)
    {
        $today = \Carbon\Carbon::today();

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
     * Scope to sort tasks by multiple criteria.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $sortCriteria Array of sort criteria with 'field' and 'direction' keys
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortByCriteria($query, array $sortCriteria)
    {
        if (empty($sortCriteria)) {
            return $query;
        }

        foreach ($sortCriteria as $criteria) {
            $field = $criteria['field'] ?? null;
            $direction = $criteria['direction'] ?? 'asc';

            if (!$field) {
                continue;
            }

            // Handle custom field sorting
            if (str_starts_with($field, 'custom_field_')) {
                $fieldId = str_replace('custom_field_', '', $field);
                $query->leftJoin('custom_field_values', function ($join) use ($fieldId) {
                    $join->on('tasks.id', '=', 'custom_field_values.task_id')
                        ->where('custom_field_values.custom_field_id', '=', $fieldId);
                })
                    ->orderBy('custom_field_values.value', $direction)
                    ->select('tasks.*');
            } else {
                $query->orderBy($field, $direction);
            }
        }

        return $query;
    }

    /**
     * Scope to sort tasks by a single field.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $field
     * @param string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortByField($query, string $field, string $direction = 'asc')
    {
        if (str_starts_with($field, 'custom_field_')) {
            $fieldId = str_replace('custom_field_', '', $field);
            return $query->leftJoin('custom_field_values', function ($join) use ($fieldId) {
                $join->on('tasks.id', '=', 'custom_field_values.task_id')
                    ->where('custom_field_values.custom_field_id', '=', $fieldId);
            })
                ->orderBy('custom_field_values.value', $direction)
                ->select('tasks.*');
        }

        return $query->orderBy($field, $direction);
    }
}
