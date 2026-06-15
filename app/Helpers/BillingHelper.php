<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Organization;
use App\Models\BusinessSetting;

class BillingHelper
{
    /**
     * Check if user is owner of any workspace.
     */
    public static function isWorkspaceOwner(User $user): bool
    {
        return Organization::where('created_by', $user->id)->exists();
    }

    /**
     * Check if user's trial is still active.
     */
    public static function isOnTrial(User $user): bool
    {
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
        $trialEnabled = BusinessSetting::get('trial_enabled', '1');
        $trialDays = (int) BusinessSetting::get('trial_days', 14);

        if ($trialEnabled && $trialDays >= 1) {
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
        // If no plan, check if on trial
        if (!$user->active_plan_id) {
            return self::isOnTrial($user);
        }

        $plan = $user->activePlan;
        if (!$plan || $plan->isUnlimited('max_workspaces')) {
            return true;
        }

        $ownedWorkspacesCount = Organization::where('created_by', $user->id)->count();
        return $ownedWorkspacesCount < $plan->max_workspaces;
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

        // If no plan, check if on trial
        if (!$owner->active_plan_id) {
            return self::isOnTrial($owner);
        }

        $plan = $owner->activePlan;
        if (!$plan || $plan->isUnlimited('max_members_per_workspace')) {
            return true;
        }

        $membersCount = $workspace->members()->count();
        return $membersCount < $plan->max_members_per_workspace;
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

        // If no plan, check if on trial
        if (!$owner->active_plan_id) {
            return self::isOnTrial($owner);
        }

        $plan = $owner->activePlan;
        if (!$plan || $plan->isUnlimited('max_projects_per_workspace')) {
            return true;
        }

        $projectsCount = $workspace->projects()->count();
        return $projectsCount < $plan->max_projects_per_workspace;
    }

    /**
     * Get remaining trial days for a user.
     */
    public static function getTrialDaysRemaining(User $user): ?int
    {
        if (!$user->trial_ends_at) {
            return null;
        }

        $remaining = now()->diffInDays($user->trial_ends_at, false);
        return max(0, (int) $remaining);
    }

    /**
     * Get user's workspace limits based on plan or trial.
     */
    public static function getWorkspaceLimits(User $user): array
    {
        if (!$user->active_plan_id) {
            // On trial - return unlimited
            return [
                'max_workspaces' => 0,
                'max_members_per_workspace' => 0,
                'max_projects_per_workspace' => 0,
                'on_trial' => self::isOnTrial($user),
            ];
        }

        $plan = $user->activePlan;
        if (!$plan) {
            return [
                'max_workspaces' => 0,
                'max_members_per_workspace' => 0,
                'max_projects_per_workspace' => 0,
                'on_trial' => false,
            ];
        }

        return [
            'max_workspaces' => $plan->max_workspaces,
            'max_members_per_workspace' => $plan->max_members_per_workspace,
            'max_projects_per_workspace' => $plan->max_projects_per_workspace,
            'on_trial' => false,
        ];
    }
}
