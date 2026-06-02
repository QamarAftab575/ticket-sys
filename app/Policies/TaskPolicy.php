<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Task;
use App\Models\User;
use App\Services\ProjectMemberService;

class TaskPolicy
{
    public function __construct(private ProjectMemberService $memberService)
    {
    }

    private function memberRole(User $user, Project $project): ?string
    {
        // Cache per request to avoid repeated DB hits across multiple policy checks
        static $cache = [];
        $key = $user->id . ':' . $project->id;
        if (!array_key_exists($key, $cache)) {
            $role = ProjectMember::where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->value('role');
            
            // If no explicit project role, check if workspace member can access public project
            if (!$role && $project->visibility === 'public_to_team') {
                $workspaceRole = $user->getWorkspaceRole($project->organization_id);
                // Workspace members (not guests) get the project's workspace_member_role
                if (in_array($workspaceRole, ['owner', 'admin', 'member'])) {
                    // Use the project's workspace_member_role setting, default to 'commenter' if not set
                    $role = $project->workspace_member_role ?? ProjectMember::ROLE_COMMENTER;
                }
            }
            
            $cache[$key] = $role;
        }
        return $cache[$key];
    }

    private function canEdit(User $user, ?Project $project): bool
    {
        // If no project (My Tasks personal task), user can edit their own tasks
        if ($project === null) {
            return true;
        }

        return in_array($this->memberRole($user, $project), [
            ProjectMember::ROLE_PROJECT_ADMIN,
            ProjectMember::ROLE_EDITOR,
        ]);
    }

    /** View: any member with access can view tasks */
    public function view(User $user, Task $task): bool
    {
        // Personal tasks (no project): only assignee or creator can view
        if ($task->project === null) {
            return $user->id === $task->assignee_id || $user->id === $task->creator_id;
        }

        return $this->memberService->canAccess($task->project, $user);
    }

    /** viewAny: passed a Project instance from the controller */
    public function viewAny(User $user, Project $project): bool
    {
        return $this->memberService->canAccess($project, $user);
    }

    /** Create: project_admin or editor only */
    public function create(User $user, Project $project): bool
    {
        return $this->canEdit($user, $project);
    }

    /** Update: project_admin or editor only, or task owner for personal tasks */
    public function update(User $user, Task $task): bool
    {
        // Personal tasks (no project): only assignee or creator can update
        if ($task->project === null) {
            return $user->id === $task->assignee_id || $user->id === $task->creator_id;
        }

        return $this->canEdit($user, $task->project);
    }

    /** Delete: project_admin or editor only, or task owner for personal tasks */
    public function delete(User $user, Task $task): bool
    {
        // Personal tasks (no project): only creator can delete
        if ($task->project === null) {
            return $user->id === $task->creator_id;
        }

        return $this->canEdit($user, $task->project);
    }

    /** Complete/reopen: project_admin or editor only, or task owner for personal tasks */
    public function complete(User $user, Task $task): bool
    {
        // Personal tasks (no project): only assignee or creator can complete
        if ($task->project === null) {
            return $user->id === $task->assignee_id || $user->id === $task->creator_id;
        }

        return $this->canEdit($user, $task->project);
    }

    /** Comment: project_admin, editor, or commenter */
    public function comment(User $user, Task $task): bool
    {
        // Personal tasks (no project): only assignee or creator can comment
        if ($task->project === null) {
            return $user->id === $task->assignee_id || $user->id === $task->creator_id;
        }

        return in_array($this->memberRole($user, $task->project), [
            ProjectMember::ROLE_PROJECT_ADMIN,
            ProjectMember::ROLE_EDITOR,
            ProjectMember::ROLE_COMMENTER,
        ]);
    }
}
