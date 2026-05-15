<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Services\ProjectMemberService;

class ProjectPolicy
{
    public function __construct(private ProjectMemberService $memberService)
    {
    }

    /**
     * Resolve the user's role for a specific project from the pivot table.
     * This is per-project — not workspace-wide.
     */
    private function projectRole(User $user, Project $project): ?string
    {
        static $cache = [];
        $key = $user->id . ':' . $project->id;
        if (!array_key_exists($key, $cache)) {
            $cache[$key] = ProjectMember::where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->value('role');
        }
        return $cache[$key];
    }

    /** Any workspace member can view public projects; private requires explicit membership. */
    public function view(User $user, Project $project): bool
    {
        return $this->memberService->canAccess($project, $user);
    }

    /** Creating a project requires workspace membership (checked in controller). */
    public function create(User $user): bool
    {
        return true;
    }

    /** Editing requires project_admin or editor role on this specific project. */
    public function update(User $user, Project $project): bool
    {
        return in_array($this->projectRole($user, $project), [
            ProjectMember::ROLE_PROJECT_ADMIN,
            ProjectMember::ROLE_EDITOR,
        ]);
    }

    /** Deleting requires project_admin role on this specific project. */
    public function delete(User $user, Project $project): bool
    {
        return $this->projectRole($user, $project) === ProjectMember::ROLE_PROJECT_ADMIN;
    }

    /** Managing members requires project_admin role on this specific project. */
    public function manageMembers(User $user, Project $project): bool
    {
        return $this->projectRole($user, $project) === ProjectMember::ROLE_PROJECT_ADMIN;
    }

    /** Alias used by ProjectMemberController::store */
    public function addMember(User $user, Project $project): bool
    {
        return $this->manageMembers($user, $project);
    }

    /** Alias used by ProjectMemberController::destroy */
    public function removeMember(User $user, Project $project): bool
    {
        return $this->manageMembers($user, $project);
    }

    /** Changing status requires project_admin or editor on this specific project. */
    public function changeStatus(User $user, Project $project): bool
    {
        return in_array($this->projectRole($user, $project), [
            ProjectMember::ROLE_PROJECT_ADMIN,
            ProjectMember::ROLE_EDITOR,
        ]);
    }

    /** Changing visibility requires project_admin on this specific project. */
    public function changeVisibility(User $user, Project $project): bool
    {
        return $this->projectRole($user, $project) === ProjectMember::ROLE_PROJECT_ADMIN;
    }

    /** Changing project lead requires project_admin on this specific project. */
    public function changeProjectLead(User $user, Project $project): bool
    {
        return $this->projectRole($user, $project) === ProjectMember::ROLE_PROJECT_ADMIN;
    }

    /** Commenting requires project_admin, editor, or commenter on this specific project. */
    public function comment(User $user, Project $project): bool
    {
        return in_array($this->projectRole($user, $project), [
            ProjectMember::ROLE_PROJECT_ADMIN,
            ProjectMember::ROLE_EDITOR,
            ProjectMember::ROLE_COMMENTER,
        ]);
    }
}
