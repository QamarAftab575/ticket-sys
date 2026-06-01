<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;

class GlobalSearchService
{
    /**
     * Search tasks and projects accessible to the user.
     * Returns recent tasks and projects based on search criteria.
     *
     * @param User $user The authenticated user
     * @param string|null $searchQuery Search keyword
     * @param int $page Page number for pagination
     * @param int $limit Number of tasks per page
     * @return array
     */
    public function search(User $user, ?string $searchQuery = null, int $page = 1, int $limit = 10): array
    {
        $tasks = $this->getAccessibleTasks($user, $searchQuery, $page, $limit);
        $projects = $this->getAccessibleProjects($user, $searchQuery);
        $hasMore = $this->hasMoreTasks($user, $searchQuery, $page, $limit);

        return [
            'tasks' => $tasks,
            'projects' => $projects,
            'has_more_tasks' => $hasMore,
        ];
    }

    /**
     * Get tasks accessible to the user.
     * A task is accessible if:
     * - User created the task
     * - Task is assigned to the user
     * - Task belongs to a project the user is a member of
     *
     * @param User $user
     * @param string|null $searchQuery
     * @param int $page
     * @param int $limit
     * @return array
     */
    private function getAccessibleTasks(User $user, ?string $searchQuery = null, int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;

        $query = Task::query()
            ->select('tasks.id', 'tasks.name', 'tasks.project_id', 'tasks.created_at')
            ->with(['project:id,name'])
            ->where(function ($q) use ($user) {
                // User created the task
                $q->where('tasks.creator_id', $user->id)
                    // OR task is assigned to user
                    ->orWhere('tasks.assignee_id', $user->id)
                    // OR task belongs to a project the user is member of
                    ->orWhereIn('tasks.project_id', function ($subQuery) use ($user) {
                        $subQuery->select('project_id')
                            ->from('project_members')
                            ->where('user_id', $user->id);
                    });
            });

        // Apply search filter if provided
        if (!empty($searchQuery)) {
            $query->where('tasks.name', 'like', "%{$searchQuery}%");
        }

        // Sort by most recent, then paginate
        $tasks = $query->orderBy('tasks.created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(fn($task) => [
                'id' => $task->id,
                'name' => $task->name,
                'project_name' => $task->project?->name,
            ])
            ->toArray();

        return $tasks;
    }

    /**
     * Get all projects the user belongs to.
     * Optionally filter by search query.
     *
     * @param User $user
     * @param string|null $searchQuery
     * @return array
     */
    private function getAccessibleProjects(User $user, ?string $searchQuery = null): array
    {
        $query = Project::query()
            ->select('projects.id', 'projects.name', 'projects.color', 'projects.icon')
            ->whereIn('projects.id', function ($subQuery) use ($user) {
                $subQuery->select('project_id')
                    ->from('project_members')
                    ->where('user_id', $user->id);
            });

        // Apply search filter if provided
        if (!empty($searchQuery)) {
            $query->where('projects.name', 'like', "%{$searchQuery}%");
        }

        $projects = $query->orderBy('projects.name', 'asc')
            ->get()
            ->map(fn($project) => [
                'id' => $project->id,
                'name' => $project->name,
                'color' => $project->color,
                'icon' => $project->icon,
            ])
            ->toArray();

        return $projects;
    }

    /**
     * Check if there are more tasks to load.
     *
     * @param User $user
     * @param string|null $searchQuery
     * @param int $page
     * @param int $limit
     * @return bool
     */
    private function hasMoreTasks(User $user, ?string $searchQuery = null, int $page = 1, int $limit = 10): bool
    {
        $offset = $page * $limit;

        $count = Task::query()
            ->where(function ($q) use ($user) {
                $q->where('tasks.creator_id', $user->id)
                    ->orWhere('tasks.assignee_id', $user->id)
                    ->orWhereIn('tasks.project_id', function ($subQuery) use ($user) {
                        $subQuery->select('project_id')
                            ->from('project_members')
                            ->where('user_id', $user->id);
                    });
            });

        if (!empty($searchQuery)) {
            $count->where('tasks.name', 'like', "%{$searchQuery}%");
        }

        return $count->offset($offset)->limit(1)->exists();
    }
}
