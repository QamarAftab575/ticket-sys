<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProjectMemberService
{
    /**
     * Add a member to a project.
     */
    public function addMember(Project $project, User $user, ?User $assignedBy = null, string $role = 'commenter', string $accessType = ProjectMember::ACCESS_TYPE_WORKSPACE_MEMBER): ProjectMember
    {
        $assignedBy = $assignedBy ?? auth()->user();

        $this->validateMemberCanBeAdded($project, $user);

        $projectMember = ProjectMember::create([
            'project_id'  => $project->id,
            'user_id'     => $user->id,
            'role'        => $role,
            'access_type' => $accessType,
            'assigned_at' => now(),
            'assigned_by' => $assignedBy->id,
        ]);

        return $projectMember;
    }

    /**
     * Remove a member from a project.
     */
    public function removeMember(Project $project, User $user): bool
    {
        return DB::transaction(function () use ($project, $user) {
            return $project->members()->detach($user->id) > 0;
        });
    }

    /**
     * Get members with their roles.
     */
    public function getMembersWithRoles(Project $project): Collection
    {
        return $project->members()
            ->with('pivot')
            ->get();
    }

    /**
     * Get projects for a member.
     */
    public function getProjectsForMember(User $user): Collection
    {
        return Project::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();
    }

    /**
     * Validate that a member can be added to a project.
     * Workspace membership is NOT required — external users can be project-only members.
     */
    public function validateMemberCanBeAdded(Project $project, User $user): void
    {
        if ($project->hasMember($user)) {
            throw new \InvalidArgumentException('User is already assigned to this project.');
        }
    }

    /**
     * Change member role.
     */
    public function changeRole(Project $project, User $member, string $role, User $user): ProjectMember
    {
        return DB::transaction(function () use ($project, $member, $role, $user) {
            $projectMember = ProjectMember::where('project_id', $project->id)
                ->where('user_id', $member->id)
                ->first();

            if (!$projectMember) {
                throw new \InvalidArgumentException('Member not found in project.');
            }

            $oldRole = $projectMember->role;
            $projectMember->update(['role' => $role]);

            // Record activity
            $projectService = new ProjectService();
            $projectService->recordActivity($project, 'role_changed', $user,
                ['user_id' => $member->id, 'role' => $oldRole],
                ['user_id' => $member->id, 'role' => $role]
            );

            return $projectMember->fresh();
        });
    }

    /**
     * Remove member by member (member leaving project).
     */
    public function removeMemberByMember(Project $project, User $member): bool
    {
        return DB::transaction(function () use ($project, $member) {
            if (!$this->canMemberLeave($project, $member)) {
                throw new \InvalidArgumentException('Member cannot leave this project.');
            }

            // Record activity
            $projectService = new ProjectService();
            $projectService->recordActivity($project, 'member_left', $member);

            return $project->members()->detach($member->id) > 0;
        });
    }

    /**
     * Check if member can leave project.
     */
    public function canMemberLeave(Project $project, User $member): bool
    {
        // Project admin who is the sole admin cannot leave
        $projectMember = ProjectMember::where('project_id', $project->id)
            ->where('user_id', $member->id)
            ->first();

        if (!$projectMember) {
            return false;
        }

        if ($projectMember->isProjectAdmin()) {
            $adminCount = ProjectMember::where('project_id', $project->id)
                ->where('role', ProjectMember::ROLE_PROJECT_ADMIN)
                ->count();
            return $adminCount > 1;
        }

        return true;
    }

    /**
     * Get the project role for a user from the pivot table.
     */
    public function getProjectRole(Project $project, User $user): ?string
    {
        return ProjectMember::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->value('role');
    }

    /**
     * Check if a user can access a project.
     * - Owner/Admin: can access ALL projects
     * - Member: can access public projects + explicit project membership
     * - Guest: only explicit project membership
     * - External: only explicit project membership
     */
    public function canAccess(Project $project, User $user): bool
    {
        // Check if user is explicit project member
        if ($project->hasMember($user)) {
            return true;
        }

        // Check workspace role
        $workspaceRole = $user->getWorkspaceRole($project->organization_id);
        
        if (!$workspaceRole) {
            return false; // No workspace membership and not project member
        }

        // Owner and Admin can access ALL projects
        if (in_array($workspaceRole, ['owner', 'admin'])) {
            return true;
        }

        // Member can access public projects
        if ($workspaceRole === 'member' && $project->visibility === 'public_to_team') {
            return true;
        }

        // Guest cannot access public projects, only explicit membership (already checked above)
        return false;
    }


}