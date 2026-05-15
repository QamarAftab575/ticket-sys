<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    /**
     * Create a new project.
     */
    public function createProject(array $data, User $creator): Project
    {
        return DB::transaction(function () use ($data, $creator) {
            $project = Project::create([
                'organization_id' => $data['organization_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'manager_id' => $data['manager_id'],
                'status' => $data['status'] ?? 'on_track',
                'visibility' => $data['visibility'] ?? 'public_to_team',
                'start_date' => $data['start_date'] ?? null,
                'target_date' => $data['target_date'] ?? null,
                'created_by' => $creator->id,
                'color' => $data['color'] ?? null,
                'icon' => $data['icon'] ?? null,
                'owner_id' => $data['owner_id'] ?? $creator->id,
                'privacy' => $data['privacy'] ?? 'public_to_team',
            ]);

            // Record audit trail
            $this->recordActivity($project, 'created', $creator);

            // Workspace owner is always project_admin
            $organization = $project->organization;
            $ownerMembership = $organization->memberships()
                ->where('role', 'owner')
                ->first();

            if ($ownerMembership) {
                $owner = \App\Models\User::find($ownerMembership->user_id);
                if ($owner) {
                    app(\App\Services\ProjectMemberService::class)->addMember(
                        $project, $owner, $creator, 'project_admin', 'workspace_member'
                    );
                }
            }

            // If creator is not the workspace owner, also add them as project_admin
            if (!$ownerMembership || $ownerMembership->user_id !== $creator->id) {
                app(\App\Services\ProjectMemberService::class)->addMember(
                    $project, $creator, $creator, 'project_admin', 'workspace_member'
                );
            }

            // Add initial members if provided (as editor role)
            if (isset($data['member_ids']) && is_array($data['member_ids'])) {
                foreach ($data['member_ids'] as $userId) {
                    if ($ownerMembership && $userId === $ownerMembership->user_id) continue;
                    $member = \App\Models\User::find($userId);
                    if ($member) {
                        app(\App\Services\ProjectMemberService::class)->addMember(
                            $project, $member, $creator, 'editor', 'direct_invite'
                        );
                    }
                }
            }

            return $project;
        });
    }

    /**
     * Update a project.
     */
    public function updateProject(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $oldValues = [];
            $newValues = [];

            // Track changes for activity log
            if (isset($data['name']) && $data['name'] !== $project->name) {
                $oldValues['name'] = $project->name;
                $newValues['name'] = $data['name'];
            }
            if (isset($data['status']) && $data['status'] !== $project->status) {
                $oldValues['status'] = $project->status;
                $newValues['status'] = $data['status'];
            }
            if (isset($data['color']) && $data['color'] !== $project->color) {
                $oldValues['color'] = $project->color;
                $newValues['color'] = $data['color'];
            }
            if (isset($data['icon']) && $data['icon'] !== $project->icon) {
                $oldValues['icon'] = $project->icon;
                $newValues['icon'] = $data['icon'];
            }

            $project->update([
                'name' => $data['name'] ?? $project->name,
                'description' => $data['description'] ?? $project->description,
                'status' => $data['status'] ?? $project->status,
                'visibility' => $data['visibility'] ?? $project->visibility,
                'start_date' => $data['start_date'] ?? $project->start_date,
                'target_date' => $data['target_date'] ?? $project->target_date,
                'color' => $data['color'] ?? $project->color,
                'icon' => $data['icon'] ?? $project->icon,
            ]);

            // Record audit trail
            if (!empty($newValues)) {
                $this->recordActivity($project, 'updated', auth()->user(), $oldValues, $newValues);
            }

            return $project->fresh();
        });
    }

    /**
     * Delete a project.
     */
    public function deleteProject(Project $project, User $user): bool
    {
        return DB::transaction(function () use ($project, $user) {
            // Remove all members (cascade handled by foreign key)
            $project->members()->detach();

            // Record audit trail
            $this->recordActivity($project, 'deleted', $user);

            // Soft delete the project
            return $project->delete();
        });
    }

    /**
     * Change the project lead.
     */
    public function changeProjectLead(Project $project, User $newLead): bool
    {
        return DB::transaction(function () use ($project, $newLead) {
            $oldLead = $project->manager_id;
            $project->update(['manager_id' => $newLead->id]);

            // Record audit trail
            $this->recordActivity($project, 'lead_changed', auth()->user(), ['manager_id' => $oldLead], ['manager_id' => $newLead->id]);

            return true;
        });
    }

    /**
     * Update project status.
     */
    public function updateStatus(Project $project, string $status): Project
    {
        $oldStatus = $project->status;
        $project->update(['status' => $status]);

        // Record audit trail
        $this->recordActivity($project, 'status_changed', auth()->user(), ['status' => $oldStatus], ['status' => $status]);

        return $project->fresh();
    }

    /**
     * Update project visibility.
     */
    public function updateVisibility(Project $project, string $visibility): Project
    {
        $oldVisibility = $project->visibility;
        $project->update(['visibility' => $visibility]);

        // Record audit trail
        $this->recordActivity($project, 'visibility_changed', auth()->user(), ['visibility' => $oldVisibility], ['visibility' => $visibility]);

        return $project->fresh();
    }

    /**
     * Update project dates.
     */
    public function updateDates(Project $project, ?string $startDate, ?string $targetDate): Project
    {
        $oldStartDate = $project->start_date;
        $oldTargetDate = $project->target_date;

        $project->update([
            'start_date' => $startDate,
            'target_date' => $targetDate,
        ]);

        // Record audit trail
        $this->recordActivity($project, 'dates_updated', auth()->user(), 
            ['start_date' => $oldStartDate, 'target_date' => $oldTargetDate],
            ['start_date' => $startDate, 'target_date' => $targetDate]
        );

        return $project->fresh();
    }

    /**
     * Get all projects for a user (including visibility).
     */
    public function getProjectsForUser(User $user): Collection
    {
        return Project::visibleTo($user)->get();
    }

    /**
     * Get visible projects for a user.
     */
    public function getVisibleProjectsForUser(User $user): Collection
    {
        return Project::visibleTo($user)->get();
    }

    /**
     * Check if a user can manage a project.
     */
    public function canUserManageProject(User $user, Project $project): bool
    {
        // Project lead can manage
        if ($project->isManager($user)) {
            return true;
        }

        // Project member with editor role can manage
        $projectMember = $project->members()
            ->where('user_id', $user->id)
            ->where('role', 'editor')
            ->first();

        return $projectMember !== null;
    }

    /**
     * Add a member to a project.
     */
    private function addMemberToProject(Project $project, User $user, User $assignedBy): void
    {
        // Default role is commenter
        $role = 'commenter';

        $project->members()->attach($user->id, [
            'role' => $role,
            'assigned_at' => now(),
            'assigned_by' => $assignedBy->id,
        ]);
    }

    /**
     * Record audit trail for project changes.
     */
    private function recordAuditTrail(Project $project, string $action, User $user): void
    {
        // Audit trail recording - can be extended to store in a dedicated table
        // For now, we rely on the timestamps and created_by fields
        // In a production system, you might want to create a project_audit_log table
    }

    /**
     * Archive a project.
     */
    public function archiveProject(Project $project, User $user): Project
    {
        return DB::transaction(function () use ($project, $user) {
            $project->update(['archived_at' => now()]);

            // Record activity
            $this->recordActivity($project, 'archived', $user);

            return $project->fresh();
        });
    }

    /**
     * Unarchive a project.
     */
    public function unarchiveProject(Project $project, User $user): Project
    {
        return DB::transaction(function () use ($project, $user) {
            $project->update(['archived_at' => null]);

            // Record activity
            $this->recordActivity($project, 'unarchived', $user);

            return $project->fresh();
        });
    }

    /**
     * Duplicate a project.
     */
    public function duplicateProject(Project $project, User $user, array $options = []): Project
    {
        return DB::transaction(function () use ($project, $user, $options) {
            $copyTasks = $options['copy_tasks'] ?? false;
            $copyMembers = $options['copy_members'] ?? false;

            // Create new project with copied settings
            $newProject = Project::create([
                'organization_id' => $project->organization_id,
                'name' => "Copy of {$project->name}",
                'description' => $project->description,
                'manager_id' => $project->manager_id,
                'status' => $project->status,
                'visibility' => $project->visibility,
                'start_date' => $project->start_date,
                'target_date' => $project->target_date,
                'created_by' => $user->id,
                'color' => $project->color,
                'icon' => $project->icon,
                'owner_id' => $user->id,
                'privacy' => $project->privacy,
            ]);

            // Copy sections
            foreach ($project->sections as $section) {
                $newProject->sections()->create([
                    'name' => $section->name,
                    'description' => $section->description,
                    'order' => $section->order,
                ]);
            }

            // Copy tasks if requested
            if ($copyTasks) {
                foreach ($project->tasks as $task) {
                    $newTask = $newProject->tasks()->create([
                        'name' => $task->name,
                        'description' => $task->description,
                        'section_id' => $newProject->sections()
                            ->where('name', $task->section->name)
                            ->first()?->id,
                        'status' => $task->status,
                        'priority' => $task->priority,
                        'assigned_to' => $task->assigned_to,
                        'start_date' => $task->start_date,
                        'due_date' => $task->due_date,
                        'created_by' => $user->id,
                    ]);
                }
            }

            // Copy members if requested
            if ($copyMembers) {
                foreach ($project->members as $member) {
                    $newProject->members()->attach($member->id, [
                        'role' => $member->pivot->role,
                        'assigned_at' => now(),
                        'assigned_by' => $user->id,
                    ]);
                }
            }

            // Record activity
            $this->recordActivity($project, 'duplicated', $user, null, ['new_project_id' => $newProject->id]);

            return $newProject;
        });
    }

    /**
     * Change project owner.
     */
    public function changeOwner(Project $project, User $newOwner, User $user): Project
    {
        return DB::transaction(function () use ($project, $newOwner, $user) {
            $oldOwner = $project->owner_id;

            $project->update(['owner_id' => $newOwner->id]);

            // Record activity
            $this->recordActivity($project, 'owner_changed', $user, ['owner_id' => $oldOwner], ['owner_id' => $newOwner->id]);

            return $project->fresh();
        });
    }

    /**
     * Change project privacy.
     */
    public function changePrivacy(Project $project, string $privacy, ?array $memberIds = null, User $user): Project
    {
        return DB::transaction(function () use ($project, $privacy, $memberIds, $user) {
            $oldPrivacy = $project->privacy;

            $project->update(['privacy' => $privacy]);

            // Handle member removal based on privacy level
            if ($privacy === 'private') {
                // Remove all members except owner
                $project->members()
                    ->where('user_id', '!=', $project->owner_id)
                    ->detach();
            } elseif ($privacy === 'specific_members' && $memberIds) {
                // Keep only specified members
                $currentMembers = $project->members->pluck('id')->toArray();
                $membersToRemove = array_diff($currentMembers, $memberIds);
                if (!empty($membersToRemove)) {
                    $project->members()->detach($membersToRemove);
                }
            }

            // Record activity
            $this->recordActivity($project, 'privacy_changed', $user, ['privacy' => $oldPrivacy], ['privacy' => $privacy]);

            return $project->fresh();
        });
    }

    /**
     * Record project activity.
     */
    public function recordActivity(Project $project, string $action, User $user, ?array $oldValue = null, ?array $newValue = null)
    {
        return $project->activities()->create([
            'user_id' => $user->id,
            'action' => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'created_at' => now(),
        ]);
    }
}
