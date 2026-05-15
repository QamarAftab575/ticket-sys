<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Section;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskDependency;
use App\Models\TaskSortPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Create a new task.
     */
    public function createTask(Project $project, array $data, User $creator): Task
    {
        // Validate task name
        $this->validateTaskName($data['name'] ?? '');

        // Validate date range if both dates are provided
        if (isset($data['start_date']) && isset($data['due_date'])) {
            $this->validateDateRange($data['start_date'], $data['due_date']);
        }

        return DB::transaction(function () use ($project, $data, $creator) {
            $task = Task::create([
                'project_id'     => $project->id,
                'name'           => $data['name'],
                'description'    => $data['description'] ?? null,
                'assignee_id'    => $data['assignee_id'] ?? $creator->id,
                'creator_id'     => $creator->id,
                'status'         => $data['status'] ?? 'to_do',
                'priority'       => $data['priority'] ?? 'medium',
                'visibility'     => $data['visibility'] ?? 'everyone',
                'section_id'     => $data['section_id'] ?? null,
                'parent_task_id' => $data['parent_task_id'] ?? null,
                'start_date'     => $data['start_date'] ?? null,
                'due_date'       => $data['due_date'] ?? null,
                'is_milestone'   => $data['is_milestone'] ?? false,
                'position'       => $data['position'] ?? 0,
            ]);

            // Log task creation activity
            TaskActivity::create([
                'task_id'       => $task->id,
                'user_id'       => $creator->id,
                'activity_type' => 'created',
                'field_name'    => null,
                'old_value'     => null,
                'new_value'     => null,
            ]);

            // Trigger notifications
            // 1. Notify assignee if task is assigned to someone else
            if ($task->assignee_id && $task->assignee_id !== $creator->id) {
                $assignee = User::find($task->assignee_id);
                if ($assignee) {
                    $this->notificationService->notifyTaskAssignment($task->load('project'), $assignee, $creator);
                }
            }

            // 2. Notify mentioned users in description
            if (!empty($task->description)) {
                $this->notificationService->notifyMentionsInDescription($task->load('project'), $creator);
            }

            return $task;
        });
    }

    /**
     * Validate task name.
     */
    private function validateTaskName(string $name): void
    {
        if (empty($name)) {
            throw ValidationException::withMessages([
                'name' => ['The task name is required.']
            ]);
        }

        if (strlen($name) > 255) {
            throw ValidationException::withMessages([
                'name' => ['The task name must not exceed 255 characters.']
            ]);
        }
    }

    /**
     * Update an existing task.
     */
    public function updateTask(Task $task, array $data): Task
    {
        // Validate task name if provided
        if (isset($data['name'])) {
            $this->validateTaskName($data['name']);
        }

        // Validate date range if both dates are provided
        $startDate = $data['start_date'] ?? $task->start_date;
        $dueDate = $data['due_date'] ?? $task->due_date;
        
        if ($startDate && $dueDate) {
            $this->validateDateRange($startDate, $dueDate);
        }

        return DB::transaction(function () use ($task, $data) {
            // Track changes for activity logging
            $changes = [];
            
            // Define fields that can be updated
            $updatableFields = [
                'name', 'description', 'assignee_id', 'status', 'priority',
                'visibility', 'section_id', 'parent_task_id', 'start_date',
                'due_date', 'is_milestone', 'position'
            ];

            foreach ($updatableFields as $field) {
                if (array_key_exists($field, $data)) {
                    $oldValue = $task->$field;
                    $newValue = $data[$field];
                    
                    // Only log if value actually changed
                    if ($oldValue != $newValue) {
                        $changes[$field] = [
                            'old' => $oldValue,
                            'new' => $newValue
                        ];
                    }
                }
            }

            // Update the task
            $task->update($data);

            // Log each field change to task_activities
            foreach ($changes as $field => $values) {
                TaskActivity::create([
                    'task_id' => $task->id,
                    'user_id' => auth()->id(),
                    'activity_type' => 'updated',
                    'field_name' => $field,
                    'old_value' => $values['old'],
                    'new_value' => $values['new'],
                ]);
            }

            // If description changed, delete storage images removed from the old content
            if (isset($data['description']) && isset($changes['description'])) {
                $this->deleteOrphanedImages(
                    $changes['description']['old'] ?? '',
                    $changes['description']['new'] ?? ''
                );
            }

            // Trigger notifications
            $actor = auth()->user();

            // 1. Notify if assignee changed
            if (isset($changes['assignee_id']) && $changes['assignee_id']['new']) {
                $newAssignee = User::find($changes['assignee_id']['new']);
                if ($newAssignee && $actor) {
                    $this->notificationService->notifyTaskAssignment($task->load('project'), $newAssignee, $actor);
                }
            }

            // 2. Notify mentioned users if description changed
            if (isset($changes['description']) && !empty($task->description) && $actor) {
                $this->notificationService->notifyMentionsInDescription($task->load('project'), $actor);
            }

            return $task->fresh();
        });
    }

    /**
     * Parse two HTML strings and hard-delete any /storage/ images
     * that exist in the old content but not in the new content.
     */
    private function deleteOrphanedImages(string $oldHtml, string $newHtml): void
    {
        $extract = function (string $html): array {
            preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/', $html, $m);
            return $m[1] ?? [];
        };

        $oldSrcs = $extract($oldHtml);
        $newSrcs = $extract($newHtml);
        $removed = array_diff($oldSrcs, $newSrcs);

        foreach ($removed as $src) {
            if (!str_contains($src, '/storage/')) continue;
            $relativePath = preg_replace('#^.*/storage/#', '', $src);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($relativePath);
            \App\Models\Attachment::where('file_path', 'like', '%' . basename($relativePath))->forceDelete();
        }
    }

    /**
     * Validate date range.
     */
    private function validateDateRange(string $startDate, string $dueDate): void
    {
        if (strtotime($startDate) > strtotime($dueDate)) {
            throw ValidationException::withMessages([
                'start_date' => ['The start date must be before or equal to the due date.']
            ]);
        }
    }

    /**
     * Complete a task.
     */
    public function completeTask(Task $task, User $user): Task
    {
        return DB::transaction(function () use ($task, $user) {
            // Store old status for activity logging
            $oldStatus = $task->status;

            // Update task with completion metadata
            $task->update([
                'status' => 'complete',
                'completed_at' => now(),
                'completed_by' => $user->id,
            ]);

            // Log status change activity
            TaskActivity::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'activity_type' => 'updated',
                'field_name' => 'status',
                'old_value' => $oldStatus,
                'new_value' => 'complete',
            ]);

            // Log completion activity
            TaskActivity::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'activity_type' => 'completed',
                'field_name' => null,
                'old_value' => null,
                'new_value' => null,
            ]);

            return $task->fresh();
        });
    }

    /**
     * Reopen a completed task.
     */
    public function reopenTask(Task $task): Task
    {
        return DB::transaction(function () use ($task) {
            // Store old status for activity logging
            $oldStatus = $task->status;

            // Clear completion metadata and set status to 'to_do'
            $task->update([
                'status' => 'to_do',
                'completed_at' => null,
                'completed_by' => null,
            ]);

            // Log status change activity
            TaskActivity::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'activity_type' => 'updated',
                'field_name' => 'status',
                'old_value' => $oldStatus,
                'new_value' => 'to_do',
            ]);

            // Log reopened activity
            TaskActivity::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'activity_type' => 'reopened',
                'field_name' => null,
                'old_value' => null,
                'new_value' => null,
            ]);

            return $task->fresh();
        });
    }

    /**
     * Assign a task to a user or unassign it.
     */
    public function assignTask(Task $task, ?User $assignee): Task
    {
        // Validate that assignee is a member of the task's project (if assignee is not null)
        if ($assignee !== null) {
            $isMember = $task->project->hasMember($assignee);
            
            if (!$isMember) {
                throw ValidationException::withMessages([
                    'assignee_id' => ['User is not a member of this project and cannot be assigned.']
                ]);
            }
        }

        return DB::transaction(function () use ($task, $assignee) {
            // Store old assignee for activity logging
            $oldAssigneeId = $task->assignee_id;

            // Update task assignee
            $task->update([
                'assignee_id' => $assignee?->id,
            ]);

            // Log assignment change activity
            TaskActivity::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'activity_type' => 'updated',
                'field_name' => 'assignee_id',
                'old_value' => $oldAssigneeId,
                'new_value' => $assignee?->id,
            ]);

            return $task->fresh();
        });
    }

    /**
     * Delete a task (soft delete).
     */
    public function deleteTask(Task $task): bool
    {
        return DB::transaction(function () use ($task) {
            // Soft delete the task
            return $task->delete();
        });
    }

    /**
     * Duplicate a task, copying all fields except assignee and dates.
     */
    public function duplicateTask(Task $task): Task
    {
        return DB::transaction(function () use ($task) {
            // Create a new task with copied fields
            $duplicatedTask = Task::create([
                'project_id' => $task->project_id,
                'section_id' => $task->section_id,
                'parent_task_id' => $task->parent_task_id,
                'name' => $task->name,
                'description' => $task->description,
                'assignee_id' => null,
                'creator_id' => auth()->id() ?? $task->creator_id,
                'status' => $task->status,
                'priority' => $task->priority,
                'visibility' => $task->visibility,
                'start_date' => null,
                'due_date' => null,
                'completed_at' => null,
                'completed_by' => null,
                'is_milestone' => $task->is_milestone,
                'position' => $task->position,
            ]);

            return $duplicatedTask;
        });
    }

    /**
     * Create a subtask under a parent task.
     */
    public function createSubtask(Task $parentTask, array $data, User $creator): Task
    {
        // Validate task name
        $this->validateTaskName($data['name'] ?? '');

        // Validate date range if both dates are provided
        if (isset($data['start_date']) && isset($data['due_date'])) {
            $this->validateDateRange($data['start_date'], $data['due_date']);
        }

        return DB::transaction(function () use ($parentTask, $data, $creator) {
            $task = Task::create([
                'project_id' => $parentTask->project_id,
                'parent_task_id' => $parentTask->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'assignee_id' => $data['assignee_id'] ?? $creator->id,
                'creator_id' => $creator->id,
                'status' => $data['status'] ?? 'to_do',
                'priority' => $data['priority'] ?? 'medium',
                'visibility' => $data['visibility'] ?? 'everyone',
                'section_id' => $data['section_id'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'is_milestone' => $data['is_milestone'] ?? false,
                'position' => $data['position'] ?? 0,
            ]);

            return $task;
        });
    }

    /**
     * Convert a standalone task to a subtask of another task.
     */
    public function convertToSubtask(Task $task, Task $parent): Task
    {
        // Validate no circular hierarchy
        if ($this->wouldCreateCircularHierarchy($task, $parent)) {
            throw ValidationException::withMessages([
                'parent_task_id' => ['Cannot convert to subtask: would create a circular hierarchy.']
            ]);
        }

        return DB::transaction(function () use ($task, $parent) {
            // Store old parent for activity logging
            $oldParentId = $task->parent_task_id;

            // Update task to set parent
            $task->update([
                'parent_task_id' => $parent->id,
            ]);

            // Log parent change activity
            TaskActivity::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'activity_type' => 'updated',
                'field_name' => 'parent_task_id',
                'old_value' => $oldParentId,
                'new_value' => $parent->id,
            ]);

            return $task->fresh();
        });
    }

    /**
     * Convert a subtask to a standalone task.
     */
    public function convertToStandaloneTask(Task $task): Task
    {
        return DB::transaction(function () use ($task) {
            // Store old parent for activity logging
            $oldParentId = $task->parent_task_id;

            // Clear parent_task_id
            $task->update([
                'parent_task_id' => null,
            ]);

            // Log parent change activity
            TaskActivity::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'activity_type' => 'updated',
                'field_name' => 'parent_task_id',
                'old_value' => $oldParentId,
                'new_value' => null,
            ]);

            return $task->fresh();
        });
    }

    /**
     * Check if setting a parent would create a circular hierarchy.
     */
    private function wouldCreateCircularHierarchy(Task $task, Task $parent): bool
    {
        // Check if parent is a descendant of task
        return $this->isDescendantOf($parent, $task);
    }

    /**
     * Check if a task is a descendant of another task.
     */
    private function isDescendantOf(Task $potentialDescendant, Task $ancestor): bool
    {
        $current = $potentialDescendant;

        // Traverse up the hierarchy
        while ($current->parent_task_id !== null) {
            if ($current->parent_task_id === $ancestor->id) {
                return true;
            }

            // Load parent and continue traversal
            $current = Task::find($current->parent_task_id);

            // Safety check: if parent not found, break
            if ($current === null) {
                break;
            }
        }

        return false;
    }

    /**
     * Get subtask progress for a task.
     */
    public function getSubtaskProgress(Task $task): array
    {
        $total = $task->subtasks()->count();

        if ($total === 0) {
            return [
                'completed' => 0,
                'total' => 0,
                'percentage' => 0,
            ];
        }

        $completed = $task->subtasks()->where('status', 'complete')->count();
        $percentage = ($completed / $total) * 100;

        return [
            'completed' => $completed,
            'total' => $total,
            'percentage' => $percentage,
        ];
    }

    /**
     * Validate that adding a dependency would not create a circular chain.
     */
    public function validateDependency(Task $task, Task $dependsOn): bool
    {
        // Check if adding this dependency would create a circular chain
        if ($this->wouldCreateCircularDependency($task, $dependsOn)) {
            throw ValidationException::withMessages([
                'depends_on_task_id' => ['Cannot add dependency: would create a circular dependency chain.']
            ]);
        }

        return true;
    }

    /**
     * Check if adding a dependency would create a circular chain.
     * Uses BFS to detect if task is reachable from dependsOn through existing dependencies.
     */
    private function wouldCreateCircularDependency(Task $task, Task $dependsOn): bool
    {
        // If task and dependsOn are the same, it's a self-reference (circular)
        if ($task->id === $dependsOn->id) {
            return true;
        }

        // Use BFS to check if task is reachable from dependsOn
        // If we can reach task from dependsOn, then adding task -> dependsOn creates a cycle
        $visited = [];
        $queue = [$dependsOn->id];

        while (!empty($queue)) {
            $currentId = array_shift($queue);

            // Skip if already visited
            if (in_array($currentId, $visited)) {
                continue;
            }

            // Mark as visited
            $visited[] = $currentId;

            // If we reached the task, there's a path from dependsOn to task
            // Adding task -> dependsOn would create a cycle
            if ($currentId === $task->id) {
                return true;
            }

            // Get all tasks that the current task depends on
            $dependencies = TaskDependency::where('task_id', $currentId)
                ->pluck('depends_on_task_id')
                ->toArray();

            // Add unvisited dependencies to the queue
            foreach ($dependencies as $depId) {
                if (!in_array($depId, $visited)) {
                    $queue[] = $depId;
                }
            }
        }

        return false;
    }

    /**
     * Add a dependency between two tasks.
     * Automatically creates the inverse relationship.
     */
    public function addDependency(Task $task, Task $dependsOn, string $type): void
    {
        // Validate that this dependency won't create a circular chain
        $this->validateDependency($task, $dependsOn);

        DB::transaction(function () use ($task, $dependsOn, $type) {
            // Determine the inverse type
            $inverseType = $type === 'blocks' ? 'blocked_by' : 'blocks';

            // Create the primary dependency
            TaskDependency::create([
                'task_id' => $task->id,
                'depends_on_task_id' => $dependsOn->id,
                'dependency_type' => $type,
            ]);

            // Create the inverse dependency
            TaskDependency::create([
                'task_id' => $dependsOn->id,
                'depends_on_task_id' => $task->id,
                'dependency_type' => $inverseType,
            ]);
        });
    }

    /**
     * Remove a dependency between two tasks.
     * Removes both the dependency and its inverse.
     */
    public function removeDependency(Task $task, Task $dependsOn): void
    {
        DB::transaction(function () use ($task, $dependsOn) {
            // Remove the dependency from task to dependsOn
            TaskDependency::where('task_id', $task->id)
                ->where('depends_on_task_id', $dependsOn->id)
                ->delete();

            // Remove the inverse dependency from dependsOn to task
            TaskDependency::where('task_id', $dependsOn->id)
                ->where('depends_on_task_id', $task->id)
                ->delete();
        });
    }

    /**
     * Move a task to a different section with optional position update.
     */
    public function moveTask(Task $task, Section $section, ?int $position = null): Task
    {
        return DB::transaction(function () use ($task, $section, $position) {
            // Store old section for activity logging
            $oldSectionId = $task->section_id;
            $oldPosition = $task->position;

            // If moving to a different section or changing position
            if ($oldSectionId !== $section->id || ($position !== null && $position !== $oldPosition)) {
                // Get all tasks in the target section (excluding the current task)
                $targetSectionTasks = Task::where('section_id', $section->id)
                    ->where('id', '!=', $task->id)
                    ->orderBy('position')
                    ->get();

                // If position is specified, shift other tasks
                if ($position !== null) {
                    // Shift tasks at or after the target position
                    foreach ($targetSectionTasks as $index => $otherTask) {
                        if ($index >= $position) {
                            $otherTask->update(['position' => $index + 1]);
                        } else {
                            $otherTask->update(['position' => $index]);
                        }
                    }
                } else {
                    // No position specified, append to end
                    $position = $targetSectionTasks->count();
                }
            }

            // Prepare update data
            $updateData = [
                'section_id' => $section->id,
            ];

            // Add position if provided or calculated
            if ($position !== null) {
                $updateData['position'] = $position;
            }

            // Update the task
            $task->update($updateData);

            // Log section change activity if section changed
            if ($oldSectionId !== $section->id) {
                TaskActivity::create([
                    'task_id' => $task->id,
                    'user_id' => auth()->id(),
                    'activity_type' => 'updated',
                    'field_name' => 'section_id',
                    'old_value' => $oldSectionId,
                    'new_value' => $section->id,
                ]);
            }

            return $task->fresh();
        });
    }

    /**
     * Reposition a task within the same section.
     */
    public function repositionTask(Task $task, ?int $position = null): Task
    {
        if ($position === null) {
            return $task;
        }

        return DB::transaction(function () use ($task, $position) {
            // Get all tasks in the same section (excluding current task)
            $sectionTasks = Task::where('section_id', $task->section_id)
                ->where('id', '!=', $task->id)
                ->orderBy('position')
                ->get();
            
            // Shift other tasks
            foreach ($sectionTasks as $index => $otherTask) {
                if ($index >= $position) {
                    $otherTask->update(['position' => $index + 1]);
                } else {
                    $otherTask->update(['position' => $index]);
                }
            }
            
            // Update the task position
            $task->update(['position' => $position]);
            
            return $task->fresh();
        });
    }

    /**
     * Get tasks filtered by tag IDs.
     * Supports filtering by one or more tags.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $tagIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterTasksByTags($query, array $tagIds)
    {
        if (empty($tagIds)) {
            return $query;
        }

        return $query->filterByTags($tagIds);
    }

    /**
     * Get tasks filtered by assignee IDs (multi-select).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $assigneeIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterTasksByAssignee($query, array $assigneeIds)
    {
        if (empty($assigneeIds)) {
            return $query;
        }

        return $query->filterByAssignee($assigneeIds);
    }

    /**
     * Get tasks filtered by priority (multi-select).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $priorities
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterTasksByPriority($query, array $priorities)
    {
        if (empty($priorities)) {
            return $query;
        }

        return $query->filterByPriority($priorities);
    }

    /**
     * Get tasks filtered by status (multi-select).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $statuses
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterTasksByStatus($query, array $statuses)
    {
        if (empty($statuses)) {
            return $query;
        }

        return $query->filterByStatus($statuses);
    }

    /**
     * Get tasks filtered by completion status.
     * Options: 'complete', 'incomplete', 'all'
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $option
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterTasksByCompletion($query, string $option)
    {
        return $query->filterByCompletion($option);
    }

    /**
     * Get tasks filtered by due date with predefined options.
     * Options: 'overdue', 'today', 'this_week', 'this_month', 'no_due_date'
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $option
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterTasksByDueDate($query, string $option)
    {
        return $query->filterByDueDate($option);
    }

    /**
     * Get tasks filtered by custom field values.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $customFieldFilters Array of ['field_id' => 'value'] pairs
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterTasksByCustomFields($query, array $customFieldFilters)
    {
        if (empty($customFieldFilters)) {
            return $query;
        }

        return $query->filterByCustomFields($customFieldFilters);
    }

    /**
     * Get tasks filtered by project ID (for My Tasks page).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $projectIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterTasksByProject($query, array $projectIds)
    {
        if (empty($projectIds)) {
            return $query;
        }

        return $query->filterByProject($projectIds);
    }

    /**
     * Apply multiple filters at once.
     * Accepts an array of filter criteria.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applyFilters($query, array $filters)
    {
        if (isset($filters['assignees']) && !empty($filters['assignees'])) {
            $query = $this->filterTasksByAssignee($query, $filters['assignees']);
        }

        if (isset($filters['due_date']) && !empty($filters['due_date'])) {
            $query = $this->filterTasksByDueDate($query, $filters['due_date']);
        }

        if (isset($filters['priorities']) && !empty($filters['priorities'])) {
            $query = $this->filterTasksByPriority($query, $filters['priorities']);
        }

        if (isset($filters['statuses']) && !empty($filters['statuses'])) {
            $query = $this->filterTasksByStatus($query, $filters['statuses']);
        }

        if (isset($filters['completion']) && !empty($filters['completion'])) {
            $query = $this->filterTasksByCompletion($query, $filters['completion']);
        }

        if (isset($filters['tags']) && !empty($filters['tags'])) {
            $query = $this->filterTasksByTags($query, $filters['tags']);
        }

        if (isset($filters['custom_fields']) && !empty($filters['custom_fields'])) {
            $query = $this->filterTasksByCustomFields($query, $filters['custom_fields']);
        }

        if (isset($filters['projects']) && !empty($filters['projects'])) {
            $query = $this->filterTasksByProject($query, $filters['projects']);
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

    /**
     * Apply sort criteria to a task query.
     *
     * @param Builder $query
     * @param array $sortCriteria Array of sort criteria with 'field' and 'direction' keys
     * @return Builder
     */
    public function applySortCriteria(Builder|Relation $query, array $sortCriteria): Builder|Relation
    {
        $sortService = app(TaskSortService::class);
        return $sortService->applySortCriteria($query, $sortCriteria);
    }

    /**
     * Save sort preference for a user in a project.
     *
     * @param User $user
     * @param Project $project
     * @param array $sortCriteria
     * @return TaskSortPreference
     */
    public function saveSortPreference(User $user, Project $project, array $sortCriteria): TaskSortPreference
    {
        $sortService = app(TaskSortService::class);
        return $sortService->saveSortPreference($user, $project, $sortCriteria);
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
        $sortService = app(TaskSortService::class);
        return $sortService->getSortPreference($user, $project);
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
        $sortService = app(TaskSortService::class);
        return $sortService->deleteSortPreference($user, $project);
    }

    /**
     * Get default sort criteria.
     *
     * @return array
     */
    public function getDefaultSortCriteria(): array
    {
        $sortService = app(TaskSortService::class);
        return $sortService->getDefaultSortCriteria();
    }

    /**
     * Get available sort options.
     *
     * @return array
     */
    public function getAvailableSortOptions(): array
    {
        $sortService = app(TaskSortService::class);
        return $sortService->getAvailableSortOptions();
    }

    /**
     * Validate sort criteria.
     *
     * @param array $sortCriteria
     * @return array
     */
    public function validateAndNormalizeSortCriteria(array $sortCriteria): array
    {
        $sortService = app(TaskSortService::class);
        return $sortService->validateAndNormalizeSortCriteria($sortCriteria);
    }

    /**
     * Move a task to a different project.
     */
    public function moveTaskToProject(Task $task, Project $newProject): Task
    {
        return DB::transaction(function () use ($task, $newProject) {
            // Update the task's project
            $task->update([
                'project_id' => $newProject->id,
                'section_id' => null, // Clear section as it may not exist in new project
            ]);

            return $task->fresh();
        });
    }
}

