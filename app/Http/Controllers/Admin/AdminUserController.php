<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    /**
     * Display a listing of all users with filters.
     */
    public function index(Request $request)
    {
        // Only show users who have created workspaces (workspace owners)
        $query = User::query()
            ->whereHas('ownedOrganizations', function ($q) {
                $q->where('is_active', true);
            });

        // Search by name or email
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        // Filter by status
        if ($request->status) {
            match($request->status) {
                'suspended' => $query->where('is_suspended', true),
                'active' => $query->where('is_suspended', false),
                default => null,
            };
        }

        // Filter by trial/paid/expired
        if ($request->type) {
            match($request->type) {
                'trial' => $query->where('trial_ends_at', '>', now())
                    ->whereNull('active_plan_id'),
                'paid' => $query->whereNotNull('active_plan_id'),
                'expired' => $query->whereNotNull('trial_ends_at')
                    ->where('trial_ends_at', '<', now())
                    ->whereNull('active_plan_id'),
                default => null,
            };
        }

        // Filter by join date
        if ($request->joined_from && $request->joined_to) {
            $query->whereBetween('created_at', [
                $request->joined_from . ' 00:00:00',
                $request->joined_to . ' 23:59:59'
            ]);
        }

        $users = $query
            ->withCount('ownedOrganizations')
            ->with('activePlan')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Transform organizations_count to match the data structure
        $users->getCollection()->transform(function ($user) {
            $user->organizations_count = $user->owned_organizations_count;
            return $user;
        });

        return inertia('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'type' => $request->type,
                'joined_from' => $request->joined_from,
                'joined_to' => $request->joined_to,
            ]
        ]);
    }

    /**
     * Display details for a specific user.
     */
    public function show(User $user)
    {
        // Get user's owned workspaces with member counts and project counts
        $workspaces = $user->ownedOrganizations()
            ->select('organizations.id', 'organizations.name', 'organizations.avatar_color', 'organizations.created_at')
            ->withCount('members', 'projects')
            ->get()
            ->map(function ($org) use ($user) {
                return [
                    'id' => $org->id,
                    'name' => $org->name,
                    'avatar_color' => $org->avatar_color,
                    'created_at' => $org->created_at,
                    'members_count' => $org->members_count,
                    'projects_count' => $org->projects_count,
                    'role' => 'owner',
                ];
            });

        // Get all projects from user's owned workspaces
        $projectStats = \App\Models\Project::whereIn('organization_id', $user->ownedOrganizations()->pluck('id'))
            ->select('id', 'name', 'organization_id')
            ->with('organization:id,name')
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'org_name' => $project->organization->name,
                    'role' => 'owner',
                ];
            });

        // Get available plans for assignment
        $availablePlans = \App\Models\Plan::where('is_active', true)->get();

        // Get user's active plan with subscription details from user table
        $activePlan = null;
        $planStartDate = null;
        if ($user->active_plan_id) {
            $activePlan = \App\Models\Plan::find($user->active_plan_id);
            $planStartDate = $user->plan_starts_at;
        }

        // Get user stats - count tasks in all user's workspaces
        $userWorkspaceIds = $user->ownedOrganizations()->pluck('organizations.id');
        $totalTasks = \App\Models\Task::whereHas('project', function ($q) use ($userWorkspaceIds) {
            $q->whereIn('organization_id', $userWorkspaceIds);
        })->count();

        // Get user stats
        $stats = [
            'total_workspaces' => $user->ownedOrganizations()->count(),
            'total_projects' => $projectStats->count(),
            'total_tasks' => $totalTasks,
            'created_at' => $user->created_at,
            'last_login' => $user->last_login_at ?? 'Never',
            'email_verified' => !is_null($user->email_verified_at),
            'is_suspended' => $user->is_suspended,
            'is_super_admin' => $user->is_super_admin,
            'trial_ends_at' => $user->trial_ends_at,
            'active_plan_id' => $user->active_plan_id,
            'plan_starts_at' => $planStartDate,
        ];

        return inertia('Admin/Users/Show', [
            'user' => $user->only('id', 'name', 'email', 'avatar', 'created_at', 'timezone'),
            'stats' => $stats,
            'workspaces' => $workspaces,
            'projects' => $projectStats,
            'availablePlans' => $availablePlans,
            'activePlan' => $activePlan,
        ]);
    }

    /**
     * Impersonate a user (login as that user).
     */
    public function impersonate(User $user)
    {
        session(['admin_impersonating' => auth()->id()]);
        auth()->login($user, true);

        return redirect()->route('dashboard')
            ->with('success', "You are now impersonating {$user->name}. Visit /admin/stop-impersonating to stop.");
    }

    /**
     * Stop impersonating a user.
     */
    public function stopImpersonating()
    {
        if ($adminId = session('admin_impersonating')) {
            $admin = User::find($adminId);
            if ($admin) {
                auth()->login($admin, true);
                session()->forget('admin_impersonating');
                return redirect()->route('admin.users.index')
                    ->with('success', 'You have stopped impersonating the user.');
            }
        }

        return redirect()->route('admin.users.index');
    }

    /**
     * Manually assign a plan to user.
     */
    public function assignPlan(Request $request, User $user)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $plan = \App\Models\Plan::findOrFail($validated['plan_id']);
        $service = new \App\Services\SubscriptionService();
        $service->assignPlanToUser($user, $plan);

        return back()->with('success', "Plan '{$plan->name}' has been assigned to {$user->email}.");
    }

    /**
     * Manually extend trial days.
     */
    public function extendTrial(Request $request, User $user)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $service = new \App\Services\SubscriptionService();
        $service->extendSubscription($user, $validated['days']);

        return back()->with('success', "Subscription extended by {$validated['days']} days for {$user->email}.");
    }

    /**
    {
        $user->update(['is_suspended' => true]);
        return back()->with('success', "User {$user->email} has been suspended.");
    }

    /**
     * Activate a suspended user account.
     */
    public function activate(User $user)
    {
        $user->update(['is_suspended' => false]);
        return back()->with('success', "User {$user->email} has been activated.");
    }

    /**
     * Permanently delete a user and all related data.
     */
    public function destroy(User $user)
    {
        $email = $user->email;
        $user->forceDelete();
        return back()->with('success', "User {$email} has been permanently deleted.");
    }
}
