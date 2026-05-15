<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class OrganizationService
{
    /**
     * Create a new organization.
     *
     * @param array $data
     * @param User $creator
     * @return Organization
     */
    public function createOrganization(array $data, User $creator): Organization
    {
        $organization = Organization::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'types' => $data['types'] ?? null,
            'created_by' => $creator->id,
            'avatar_color' => $data['avatar_color'] ?? '#3B82F6',
            'is_active' => true,
        ]);

        return $organization;
    }

    /**
     * Add a member to an organization and assign their Spatie workspace role.
     */
    public function addMember(Organization $organization, User $user, string $role = 'member'): OrganizationMembership
    {
        // DB stores 'owner' or 'member' (without prefix)
        $dbRole = str_starts_with($role, 'workspace_') ? substr($role, 10) : $role;

        $membership = $organization->memberships()->updateOrCreate(
            ['user_id' => $user->id],
            ['role' => $dbRole, 'joined_at' => now()]
        );

        // Spatie role uses workspace_ prefix, scoped to this workspace
        $spatieRole = str_starts_with($role, 'workspace_') ? $role : "workspace_{$role}";
        setPermissionsTeamId($organization->id);
        $user->syncRoles([$spatieRole]);

        return $membership;
    }

    /**
     * Check if a user is a member of an organization.
     */
    public function isWorkspaceMember(Organization $organization, User $user): bool
    {
        return $organization->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Remove a member from an organization and revoke their Spatie workspace role.
     */
    public function removeMember(Organization $organization, User $user): bool
    {
        setPermissionsTeamId($organization->id);
        $user->removeRole('workspace_owner');
        $user->removeRole('workspace_member');

        return $organization->removeMember($user);
    }

    /**
     * Update a member's role in an organization.
     *
     * @param Organization $organization
     * @param User $user
     * @param string $role
     * @return bool
     */
    public function updateMemberRole(Organization $organization, User $user, string $role): bool
    {
        return (bool) $organization->memberships()
            ->where('user_id', $user->id)
            ->update(['role' => $role]);
    }

    /**
     * Get all organizations for a user.
     *
     * @param User $user
     * @return Collection
     */
    public function getOrganizationsForUser(User $user): Collection
    {
        return $user->organizations()
            ->where('organizations.is_active', true)
            ->whereNull('organization_memberships.deleted_at')
            ->get();
    }

    /**
     * Check if a user can manage an organization.
     *
     * @param User $user
     * @param Organization $organization
     * @return bool
     */
    public function canUserManage(User $user, Organization $organization): bool
    {
        if ($user->hasRole(['admin', 'super-admin'])) {
            return true;
        }

        return $organization->isAdmin($user);
    }

    /**
     * Check if a user can access an organization.
     *
     * @param User $user
     * @param Organization $organization
     * @return bool
     */
    public function canUserAccess(User $user, Organization $organization): bool
    {
        if ($user->hasRole(['admin', 'super-admin'])) {
            return true;
        }

        return $organization->hasMember($user);
    }

    /**
     * Delete an organization and all its data (projects, tasks, members, invitations).
     */
    public function deleteOrganization(Organization $organization): bool
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($organization) {
            // Load all projects to cascade-delete their children explicitly
            // (soft-delete doesn't trigger DB-level cascades)
            $organization->projects()->with('tasks')->each(function ($project) {
                // Delete task-level children
                $project->tasks()->each(function ($task) {
                    $task->customFieldValues()->delete();
                    $task->comments()->delete();
                    $task->attachments()->delete();
                    $task->delete();
                });
                $project->sections()->delete();
                $project->customFields()->delete();
                $project->members()->detach();
                $project->delete();
            });

            // Remove all memberships and invitations
            $organization->memberships()->delete();
            $organization->invitations()->delete();

            // Soft-delete the organization itself
            return $organization->delete();
        });
    }

    /**
     * Update an organization.
     *
     * @param Organization $organization
     * @param array $data
     * @return Organization
     */
    public function updateOrganization(Organization $organization, array $data): Organization
    {
        $organization->update([
            'name' => $data['name'] ?? $organization->name,
            'description' => $data['description'] ?? $organization->description,
            'types' => $data['types'] ?? $organization->types,
            'avatar_color' => $data['avatar_color'] ?? $organization->avatar_color,
        ]);

        return $organization;
    }
}
