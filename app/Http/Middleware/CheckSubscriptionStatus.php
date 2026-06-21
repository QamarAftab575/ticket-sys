<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\BillingHelper;
use App\Models\Organization;

class CheckSubscriptionStatus
{
    /**
     * Handle an incoming request.
     * 
     * Checks if user's subscription is expired/suspended and redirects appropriately:
     * - If account is suspended: redirect to subscriptions for restricted routes
     * - If owner with suspended account trying to access workspace: redirect to subscriptions
     * - If member trying to access workspace whose owner is suspended: show suspension notice
     * 
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        // Only check if user is authenticated and not super admin
        if (!$user || $user->isSuperAdmin()) {
            return $next($request);
        }

        // Define routes that should redirect suspended users
        $restrictedRoutes = [
            'dashboard',
            'my-tasks.index',
            'inbox.index',
            'reports.index',
            'reports.data',
            'reports.assignees',
            'projects.index',
            'projects.create',
            'projects.store',
            'projects.show',
            'projects.edit',
            'projects.update',
            'projects.destroy',
            'projects.updateStatus',
            'projects.shareData',
            'projects.updateVisibility',
            'projects.updateWorkspaceMemberRole',
            'projects.changeProjectLead',
            'projects.members.index',
            'projects.members.store',
            'projects.members.destroy',
            'projects.members.changeRole',
            'projects.members.leave',
            'projects.invitations.index',
            'projects.invitations.store',
            'projects.invitations.resend',
            'projects.invitations.destroy',
            'projects.settings.show',
            'projects.settings.updateGeneral',
            'projects.settings.updatePrivacy',
            'projects.archive',
            'projects.unarchive',
            'projects.delete',
            'projects.duplicate',
            'projects.activity.index',
            'projects.views.tasks',
            'projects.views.files',
            'projects.views.dashboard',
            'projects.views.preferences.save',
            'projects.views.preferences.get',
        ];

        // Check if current route is in restricted list
        $isRestrictedRoute = false;
        foreach ($restrictedRoutes as $routeName) {
            if ($request->routeIs($routeName)) {
                $isRestrictedRoute = true;
                break;
            }
        }

        // GLOBAL CHECK: If user's account is suspended and accessing restricted routes
        if (BillingHelper::isAccountSuspended($user)) {
            // Allow access to settings/subscriptions and related pages
            $allowedRoutes = [
                'subscriptions.show',
                'subscriptions.change-plan',
                'subscriptions.cancel',
                'subscriptions.rebuy',
                'subscription.checkout-session',
                'subscription.payment-success',
                'settings',
                'settings.save',
                'profile.show',
                'profile.update',
                'logout',
            ];
            
            $isAllowed = false;
            foreach ($allowedRoutes as $routeName) {
                if ($request->routeIs($routeName)) {
                    $isAllowed = true;
                    break;
                }
            }
            
            if (!$isAllowed && $isRestrictedRoute) {
                return redirect()->route('subscriptions.show')
                    ->with('error', 'Your subscription has expired and grace period has ended. Your workspace access is restricted. Please renew immediately.');
            }
        }

        // Get workspace from route parameter (usually 'workspace' or 'organization')
        $workspace = null;
        
        // Try common parameter names
        if ($request->route('workspace')) {
            $workspace = Organization::findOrFail($request->route('workspace'));
        } elseif ($request->route('organization')) {
            $workspace = Organization::findOrFail($request->route('organization'));
        } elseif ($request->route('org')) {
            $workspace = Organization::findOrFail($request->route('org'));
        } elseif ($request->route('project')) {
            // Also check project's workspace
            $project = $request->route('project');
            if ($project && method_exists($project, 'organization')) {
                $workspace = $project->organization;
            }
        }

        // If we found a workspace, check permissions
        if ($workspace) {
            // Check if current user is the workspace owner
            if (BillingHelper::isWorkspaceOwner($user, $workspace)) {
                // Owner with suspended account - redirect to subscriptions
                if (BillingHelper::isAccountSuspended($user)) {
                    return redirect()->route('subscriptions.show')
                        ->with('error', 'Your subscription has expired and grace period has ended. Please renew your subscription to continue using this workspace.');
                }
            } else {
                // User is a member - check if workspace owner is suspended
                $owner = $workspace->creator;
                if ($owner && BillingHelper::isOwnerAccountSuspended($owner)) {
                    // Workspace owner's account is suspended - show notice and redirect
                    return redirect()->route('dashboard')
                        ->with('error', 'This workspace is currently unavailable because the owner\'s subscription has expired. Please contact the workspace owner.');
                }
            }
        }

        return $next($request);
    }
}
