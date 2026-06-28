<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\BusinessSetting;

class BillingHelper
{
    /**
     * Check if user is owner of a workspace.
     * If workspace is provided, checks if user is owner of that specific workspace.
     * If workspace is not provided, checks if user is owner of any workspace.
     */
    public static function isWorkspaceOwner(User $user, ?Organization $workspace = null): bool
    {
        if ($workspace) {
            // Check if user is owner of specific workspace
            return $workspace->created_by === $user->id;
        }

        // Check if user owns any workspace
        return Organization::where('created_by', $user->id)->exists();
    }

    /**
     * Get the current active workspace for the authenticated user.
     * Returns the workspace stored in user's active_workspace_id field.
     * If no active workspace is set, returns the first available workspace.
     * Returns null if user has no workspaces.
     * 
     * @param User|null $user - User instance (defaults to authenticated user)
     * @return Organization|null
     */
    public static function getActiveWorkspace(?User $user = null): ?Organization
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return null;
        }

        // First, try to get the workspace set as active_workspace_id
        if ($user->active_workspace_id) {
            $workspace = Organization::find($user->active_workspace_id);
            
            // Verify user still has access to this workspace
            if ($workspace && $user->organizationMemberships()
                ->where('organization_id', $workspace->id)
                ->whereNull('deleted_at')
                ->exists()) {
                return $workspace;
            }
        }

        // If no active workspace or access revoked, get first available workspace
        return $user->organizations()
            ->where('organizations.is_active', true)
            ->whereNull('organization_memberships.deleted_at')
            ->first();
    }

    /**
     * Check if user is a member of a workspace.
     * 
     * @param Organization $workspace
     * @param User|null $user - User instance (defaults to authenticated user)
     * @return bool
     */
    public static function isWorkspaceMember(Organization $workspace, ?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return false;
        }

        return $user->organizationMemberships()
            ->where('organization_id', $workspace->id)
            ->whereNull('deleted_at')
            ->exists();
    }

    /**
     * Check if user is a trial member of a workspace (owner's trial affects all members).
     * 
     * @param Organization $workspace
     * @return bool
     */
    public static function isWorkspaceOnTrial(Organization $workspace): bool
    {
        $owner = $workspace->creator;
        return $owner && self::isOnTrial($owner);
    }

    /**
     * Check if user is a paid member of a workspace (owner has active paid plan).
     * 
     * @param Organization $workspace
     * @return bool
     */
    public static function isWorkspacePaid(Organization $workspace): bool
    {
        $owner = $workspace->creator;
        return $owner && self::hasActivePlan($owner);
    }

    /**
     * Check if user's trial is still active.
     * Trial is only active if:
     * 1. trial_ends_at is set and in the future
     * 2. AND user does NOT have an active paid plan
     */
    public static function isOnTrial(User $user): bool
    {
        // If user has active paid plan, they're not on trial anymore
        if (self::hasActivePlan($user)) {
            return false;
        }

        // Trial is active if trial_ends_at exists and is in the future
        return $user->trial_ends_at && $user->trial_ends_at->isFuture();
    }

    /**
     * Check if user has an active paid plan.
     */
    public static function hasActivePlan(User $user): bool
    {
        return !is_null($user->active_plan_id) && !is_null($user->stripe_subscription_id);
    }

    /**
     * Get the active plan for a user.
     * Returns the complete Plan object if user has an active paid plan.
     * Returns null if user is on trial or has no plan.
     * 
     * @param User $user
     * @return Plan|null
     */
    public static function getUserPlan(User $user): ?Plan
    {
        // Check if user has active paid plan
        if ($user->active_plan_id && self::hasActivePlan($user)) {
            return $user->activePlan;
        }

        // User is on trial or has no plan
        return null;
    }

    /**
     * Get all plan information for a user.
     * Returns complete information about user's current plan or trial status.
     * 
     * @param User $user
     * @return array
     */
    public static function getUserPlanInfo(User $user): array
    {
        // If user has active paid plan
        if ($user->active_plan_id && self::hasActivePlan($user)) {
            $plan = $user->activePlan;
            $currentSubscription = $user->currentSubscription();
            
            return [
                'status' => 'active',
                'type' => 'paid',
                'plan' => $plan,
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'plan_slug' => $plan->slug,
                'price' => $plan->price,
                'billing_cycle' => $plan->billing_cycle,
                'features' => $plan->features,
                'limits' => [
                    'max_workspaces' => $plan->max_workspaces,
                    'max_members_per_workspace' => $plan->max_members_per_workspace,
                    'max_projects_per_workspace' => $plan->max_projects_per_workspace,
                ],
                'subscription_id' => $user->stripe_subscription_id,
                'subscription_ends_at' => $currentSubscription?->expires_at,
                'days_remaining' => self::getDaysRemaining($user),
            ];
        }

        // If user is on trial
        if (self::isOnTrial($user)) {
            return [
                'status' => 'active',
                'type' => 'trial',
                'plan' => null,
                'plan_name' => 'Trial',
                'features' => ['Full access during trial period'],
                'limits' => [
                    'max_workspaces' => (int) env('WORKSPACE_FOR_TRIAL_USERS', 1),
                    'max_members_per_workspace' => (int) env('MEMBERS_PER_PROJECT_FOR_TRIAL_USERS', 20),
                    'max_projects_per_workspace' => (int) env('PROJECT_PER_WORKSPACE_FOR_TRIAL_USERS', 5),
                ],
                'trial_ends_at' => $user->trial_ends_at,
                'days_remaining' => self::getDaysRemaining($user),
            ];
        }

        // No plan or trial
        return [
            'status' => 'inactive',
            'type' => 'none',
            'plan' => null,
            'plan_name' => null,
            'features' => [],
            'limits' => [
                'max_workspaces' => 0,
                'max_members_per_workspace' => 0,
                'max_projects_per_workspace' => 0,
            ],
            'days_remaining' => null,
        ];
    }

    /**
     * Check if user's plan is on trial.
     */
    public static function isPlanOnTrial(User $user): bool
    {
        $planInfo = self::getUserPlanInfo($user);
        return $planInfo['type'] === 'trial';
    }

    /**
     * Check if user's plan is paid.
     */
    public static function isPlanPaid(User $user): bool
    {
        $planInfo = self::getUserPlanInfo($user);
        return $planInfo['type'] === 'paid';
    }

    

    /**
     * Check if user needs to upgrade (trial expired, no plan).
     * Only show upgrade modal to workspace owners.
     */
    public static function needsUpgrade(User $user): bool
    {
        return self::isWorkspaceOwner($user)
            && !self::isOnTrial($user)
            && !self::hasActivePlan($user);
    }

    /**
     * Assign trial to new user on registration.
     */
    public static function assignTrial(User $user): void
    {
        $trialDays = (int) env('FREE_TRIAL_FOR_NEW_USERS', 10);

        if ($trialDays >= 1) {
            $user->update([
                'trial_ends_at' => now()->addDays($trialDays),
            ]);
        }
    }

    /**
     * Check if user can create a new workspace.
     */
    public static function canCreateWorkspace(User $user): bool
    {
        // Super admin can always create workspace
        if ($user->is_super_admin) {
            return true;
        }

        // If user has active paid plan
        if ($user->active_plan_id && self::hasActivePlan($user)) {
            $plan = $user->activePlan;
            if (!$plan || $plan->isUnlimited('max_workspaces')) {
                return true;
            }

            $ownedWorkspacesCount = Organization::where('created_by', $user->id)->count();
            return $ownedWorkspacesCount < $plan->max_workspaces;
        }

        // Trial user: check if on trial and within trial workspaces limit
        if (self::isOnTrial($user)) {
            $allowedWorkspaces = (int) env('WORKSPACE_FOR_TRIAL_USERS', 1);
            $ownedWorkspacesCount = Organization::where('created_by', $user->id)->count();
            return $ownedWorkspacesCount < $allowedWorkspaces;
        }

        // No plan and no trial: deny access
        return false;
    }

    /**
     * Check if user can add a member to a workspace.
     */
    public static function canAddMember(Organization $workspace): bool
    {
        $owner = $workspace->creator;
        if (!$owner) {
            return false;
        }

        // Super admin can always add members
        if ($owner->is_super_admin) {
            return true;
        }

        // If user has active paid plan
        if ($owner->active_plan_id && self::hasActivePlan($owner)) {
            $plan = $owner->activePlan;
            if (!$plan || $plan->isUnlimited('max_members_per_workspace')) {
                return true;
            }

            $membersCount = $workspace->members()->count();
            return $membersCount < $plan->max_members_per_workspace;
        }

        // Trial user: check if on trial and within trial members limit
        if (self::isOnTrial($owner)) {
            $allowedMembers = (int) env('MEMBERS_PER_PROJECT_FOR_TRIAL_USERS', 20);
            $membersCount = $workspace->members()->count();
            return $membersCount < $allowedMembers;
        }

        // No plan and no trial: deny access
        return false;
    }

    /**
     * Check if user can create a project in a workspace.
     */
    public static function canCreateProject(Organization $workspace): bool
    {
        $owner = $workspace->creator;
        if (!$owner) {
            return false;
        }

        // Super admin can always create projects
        if ($owner->is_super_admin) {
            return true;
        }

        // If user has active paid plan
        if ($owner->active_plan_id && self::hasActivePlan($owner)) {
            $plan = $owner->activePlan;
            if (!$plan || $plan->isUnlimited('max_projects_per_workspace')) {
                return true;
            }

            $projectsCount = $workspace->projects()->count();
            return $projectsCount < $plan->max_projects_per_workspace;
        }

        // Trial user: check if on trial and within trial projects limit
        if (self::isOnTrial($owner)) {
            $allowedProjects = (int) env('PROJECT_PER_WORKSPACE_FOR_TRIAL_USERS', 5);
            $projectsCount = $workspace->projects()->count();
            return $projectsCount < $allowedProjects;
        }

        // No plan and no trial: deny access
        return false;
    }

    /**
     * Get remaining days for trial or paid plan.
     * Returns days remaining until trial expires or subscription ends.
     * Returns null if user has no trial or active plan.
     * 
     * Can return negative numbers if expired (use getExpiryStatus for grace period info).
     */
    public static function getDaysRemaining(User $user): ?int
    {
        $expiryDate = null;

        // Determine which expiry date to use (paid plan takes priority)
        if ($user->active_plan_id && self::hasActivePlan($user)) {
            // Get the latest subscription for this plan (including recently expired ones)
            $subscription = $user->subscriptions()
                ->where('plan_id', $user->active_plan_id)
                ->where('status', 'active')
                ->latest('started_at')
                ->first();
                
            if ($subscription && $subscription->expires_at) {
                $expiryDate = $subscription->expires_at;
            }
        } elseif ($user->trial_ends_at) {
            $expiryDate = $user->trial_ends_at;
        }

        if (!$expiryDate) {
            return null;
        }

        // Calculate days remaining (can be negative if expired)
        $remaining = now()->diffInDays($expiryDate, false);
        return (int) $remaining;
    }

    /**
     * Get the expiry/grace period status for a user.
     * Returns comprehensive information about subscription state including grace period.
     * 
     * Returns array with keys:
     * - status: 'active', 'warning', 'expired_grace', 'suspended'
     * - days_remaining: Days until expiry (negative if expired)
     * - days_in_grace_period: Days remaining in grace period (0 if not in grace)
     * - expires_at: Expiry date
     * - grace_period_ends_at: When grace period ends (null if not applicable)
     * - grace_period_days: Total grace period days configured
     * 
     * @param User $user
     * @return array
     */
    public static function getExpiryStatus(User $user): array
    {
        $daysRemaining = self::getDaysRemaining($user);
        $gracePeriodDays = (int) env('BONUS_DAYS_AFTER_LIMIT', 2);
        $expiryDate = null;

        // Determine expiry date (paid plan takes priority)
        if ($user->active_plan_id && self::hasActivePlan($user)) {
            // Get the latest subscription for this plan (including recently expired ones)
            $subscription = $user->subscriptions()
                ->where('plan_id', $user->active_plan_id)
                ->where('status', 'active')
                ->latest('started_at')
                ->first();
                
            if ($subscription && $subscription->expires_at) {
                $expiryDate = $subscription->expires_at;
            }
        } elseif ($user->trial_ends_at) {
            $expiryDate = $user->trial_ends_at;
        }

        // No active plan or trial
        if (!$expiryDate || $daysRemaining === null) {
            return [
                'status' => 'inactive',
                'days_remaining' => null,
                'days_in_grace_period' => 0,
                'expires_at' => null,
                'grace_period_ends_at' => null,
                'grace_period_days' => $gracePeriodDays,
            ];
        }

        // Calculate grace period end date
        $gracePeriodEndsAt = $expiryDate->copy()->addDays($gracePeriodDays);
        $now = now();

        // STATE 1: Still before expiry (days remaining > 0)
        if ($daysRemaining > 0) {
            return [
                'status' => 'active',
                'days_remaining' => $daysRemaining,
                'days_in_grace_period' => 0,
                'expires_at' => $expiryDate,
                'grace_period_ends_at' => $gracePeriodEndsAt,
                'grace_period_days' => $gracePeriodDays,
            ];
        }

        // STATE 2: Exactly 0 days remaining (expiry day is today, but not yet expired)
        // Only show warning if we haven't passed the expiry datetime yet
        if ($daysRemaining === 0 && $now->lessThanOrEqualTo($expiryDate)) {
            return [
                'status' => 'warning',
                'days_remaining' => 0,
                'days_in_grace_period' => 0,
                'expires_at' => $expiryDate,
                'grace_period_ends_at' => $gracePeriodEndsAt,
                'grace_period_days' => $gracePeriodDays,
                'message' => 'Your subscription will expire in 1 day. Please renew your subscription to avoid service interruption.',
            ];
        }

        // STATE 3: Expired but within grace period
        // Check if current time is after expiry and before or equal to grace period end
        if ($now->greaterThan($expiryDate) && $now->lessThanOrEqualTo($gracePeriodEndsAt)) {
            // Calculate integer days remaining in grace period
            $daysInGrace = (int) ceil($now->diffInSeconds($gracePeriodEndsAt) / 86400); // 86400 seconds per day
            if ($daysInGrace < 0) $daysInGrace = 0;
            
            return [
                'status' => 'expired_grace',
                'days_remaining' => $daysRemaining,
                'days_in_grace_period' => $daysInGrace,
                'expires_at' => $expiryDate,
                'grace_period_ends_at' => $gracePeriodEndsAt,
                'grace_period_days' => $gracePeriodDays,
                'message' => "Your subscription has expired. Please renew your subscription. Your account may be suspended after {$daysInGrace} days.",
            ];
        }

        // STATE 4: Grace period has ended - account is suspended
        return [
            'status' => 'suspended',
            'days_remaining' => $daysRemaining,
            'days_in_grace_period' => 0,
            'expires_at' => $expiryDate,
            'grace_period_ends_at' => $gracePeriodEndsAt,
            'grace_period_days' => $gracePeriodDays,
            'message' => 'Your subscription has expired and grace period has ended. Your workspace access is restricted. Please renew immediately.',
        ];
    }

    /**
     * Check if user's account is suspended due to expired subscription/trial.
     * Only returns true if grace period has ended.
     * 
     * @param User $user
     * @return bool
     */
    public static function isAccountSuspended(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        $status = self::getExpiryStatus($user);
        return $status['status'] === 'suspended';
    }

    /**
     * Check if user is in grace period (expired but within grace days).
     * 
     * @param User $user
     * @return bool
     */
    public static function isInGracePeriod(User $user): bool
    {
        $status = self::getExpiryStatus($user);
        return $status['status'] === 'expired_grace';
    }

    /**
     * Check if user's subscription needs renewal warning.
     * Returns true if:
     * - 1 day or less remaining before expiry, OR
     * - In grace period, OR
     * - Account suspended
     * 
     * @param User $user
     * @return bool
     */
    public static function needsRenewalWarning(User $user): bool
    {
        $status = self::getExpiryStatus($user);
        return in_array($status['status'], ['warning', 'expired_grace', 'suspended']);
    }

    /**
     * Check if user can access workspace they own.
     * Returns false if account is suspended (grace period ended).
     * 
     * Only workspace owners are restricted. Members can continue accessing workspaces
     * even if owner's subscription is expired.
     * 
     * @param User $user
     * @param Organization|null $workspace
     * @return bool
     */
    public static function canAccessOwnedWorkspace(User $user, ?Organization $workspace = null): bool
    {
        // Only check suspension if user is owner of the workspace
        if ($workspace && !self::isWorkspaceOwner($user, $workspace)) {
            // Members can always access, only owners are restricted
            return true;
        }

        // Owner check: if account is suspended, cannot access owned workspaces
        if (self::isAccountSuspended($user)) {
            return false;
        }

        return true;
    }



    /**
     * Get trial user's current usage
     * Returns array with current counts
     * 
     * @param User $user
     * @return array
     */
    public static function getUserTrialUsage(User $user): array
    {
        return [
            'workspaces' => [
                'current' => Organization::where('created_by', $user->id)->count(),
                'allowed' => (int) env('WORKSPACE_FOR_TRIAL_USERS', 1),
            ],
            'projects' => [
                'current' => \App\Models\Project::whereHas('organization', function ($query) use ($user) {
                    $query->where('created_by', $user->id);
                })->count(),
                'allowed' => (int) env('PROJECT_PER_WORKSPACE_FOR_TRIAL_USERS', 5),
            ],
            'members' => [
                'current' => \App\Models\OrganizationMembership::whereHas('organization', function ($query) use ($user) {
                    $query->where('created_by', $user->id);
                })->count(),
                'allowed' => (int) env('MEMBERS_PER_PROJECT_FOR_TRIAL_USERS', 20),
            ],
            'trial_ends_at' => $user->trial_ends_at,
        ];
    }

    /**
     * Get remaining limits for trial or paid plan user
     * Returns array with remaining counts
     * Works for both trial users and users with paid plans
     * 
     * @param User $user
     * @return array
     */
    public static function getUserRemainingLimits(User $user): array
    {
        $workspacesUsed = Organization::where('created_by', $user->id)->count();
        $projectsUsed = \App\Models\Project::whereHas('organization', function ($query) use ($user) {
            $query->where('created_by', $user->id);
        })->count();
        $membersUsed = \App\Models\OrganizationMembership::whereHas('organization', function ($query) use ($user) {
            $query->where('created_by', $user->id);
        })->count();

        // If user has active paid plan
        if ($user->active_plan_id && self::hasActivePlan($user)) {
            $plan = $user->activePlan;
            
            return [
                'workspaces' => $plan->isUnlimited('max_workspaces') 
                    ? -1 // -1 means unlimited
                    : max(0, $plan->max_workspaces - $workspacesUsed),
                'projects' => $plan->isUnlimited('max_projects_per_workspace')
                    ? -1 // -1 means unlimited
                    : max(0, $plan->max_projects_per_workspace - $projectsUsed),
                'members' => $plan->isUnlimited('max_members_per_workspace')
                    ? -1 // -1 means unlimited
                    : max(0, $plan->max_members_per_workspace - $membersUsed),
                'type' => 'paid',
                'plan_name' => $plan->name,
            ];
        }

        // If user is on trial
        if (self::isOnTrial($user)) {
            $allowedWorkspaces = (int) env('WORKSPACE_FOR_TRIAL_USERS', 1);
            $allowedProjects = (int) env('PROJECT_PER_WORKSPACE_FOR_TRIAL_USERS', 5);
            $allowedMembers = (int) env('MEMBERS_PER_PROJECT_FOR_TRIAL_USERS', 20);

            return [
                'workspaces' => max(0, $allowedWorkspaces - $workspacesUsed),
                'projects' => max(0, $allowedProjects - $projectsUsed),
                'members' => max(0, $allowedMembers - $membersUsed),
                'type' => 'trial',
                'expires_at' => $user->trial_ends_at,
            ];
        }

        // No plan or trial
        return [
            'workspaces' => 0,
            'projects' => 0,
            'members' => 0,
            'type' => 'none',
        ];
    }

    /**
     * Get all trial limits from env
     * Returns array with all configured limits
     * 
     * @return array
     */
    public static function getTrialLimits(): array
    {
        return [
            'trial_days' => (int) env('FREE_TRIAL_FOR_NEW_USERS', 10),
            'workspaces' => (int) env('WORKSPACE_FOR_TRIAL_USERS', 1),
            'projects_per_workspace' => (int) env('PROJECT_PER_WORKSPACE_FOR_TRIAL_USERS', 5),
            'members_per_project' => (int) env('MEMBERS_PER_PROJECT_FOR_TRIAL_USERS', 20),
        ];
    }

    /**
     * Check if trial user has hit limit
     * Returns true if user reached limit for specific resource
     * 
     * @param User $user
     * @param string $resource ('workspace', 'project', 'member')
     * @return bool
     */
    public static function hasReachedLimit(User $user, string $resource): bool
    {
        $remaining = self::getUserRemainingLimits($user);

        return match ($resource) {
            'workspace' => $remaining['workspaces'] <= 0,
            'project' => $remaining['projects'] <= 0,
            'member' => $remaining['members'] <= 0,
            default => false,
        };
    }

    /**
     * Get all workspaces owned by user that are currently suspended.
     * A workspace is suspended if the owner's account is suspended (grace period ended).
     * 
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getSuspendedOwnedWorkspaces(User $user)
    {
        if (!self::isAccountSuspended($user)) {
            return collect([]);
        }

        return Organization::where('created_by', $user->id)->get();
    }

    /**
     * Check if owner's subscription is suspended (blocking access).
     * This is used to check if workspace access should be blocked for members.
     * 
     * @param User $owner
     * @return bool
     */
    public static function isOwnerAccountSuspended(User $owner): bool
    {
        return self::isAccountSuspended($owner);
    }
}
