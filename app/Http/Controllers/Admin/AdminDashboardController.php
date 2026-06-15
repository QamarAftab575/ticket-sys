<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard with system statistics.
     */
    public function index()
    {
        // Basic counts
        $totalUsers = User::count();
        $totalWorkspaces = Organization::count();
        $totalProjects = Project::count();
        $totalTasks = Task::count();

        // Signup metrics
        $newUsersToday = User::whereDate('created_at', today())->count();
        $newUsersWeek = User::whereBetween('created_at', [now()->startOfWeek(), now()])->count();
        $newUsersMonth = User::whereBetween('created_at', [now()->startOfMonth(), now()])->count();

        // Trial metrics
        $activeTrials = User::where('trial_ends_at', '>', now())->count();
        $expiredTrials = User::whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->count();

        // Subscription metrics
        $paidSubscribers = 0; // Will be calculated based on your payment system
        $monthlyRevenue = $this->calculateMonthlyRevenue();

        // User growth last 30 days (daily breakdown)
        $userGrowth = $this->getUserGrowthLast30Days();

        // Revenue last 12 months
        $revenueChartData = $this->getRevenueLast12Months();

        // Recent signups (last 10 users)
        $recentSignups = User::orderBy('created_at', 'desc')
            ->limit(10)
            ->select('id', 'name', 'email', 'created_at')
            ->get();

        return inertia('Admin/Dashboard', [
            'stats' => [
                'total_users'       => $totalUsers,
                'total_workspaces'  => $totalWorkspaces,
                'total_projects'    => $totalProjects,
                'total_tasks'       => $totalTasks,
                'new_users_today'   => $newUsersToday,
                'new_users_week'    => $newUsersWeek,
                'new_users_month'   => $newUsersMonth,
                'active_trials'     => $activeTrials,
                'expired_trials'    => $expiredTrials,
                'paid_subscribers'  => $paidSubscribers,
                'monthly_revenue'   => $monthlyRevenue,
            ],
            'userGrowthChart'   => $userGrowth,
            'revenueChart'      => $revenueChartData,
            'recentSignups'     => $recentSignups,
        ]);
    }

    /**
     * Calculate monthly revenue from active subscriptions.
     */
    private function calculateMonthlyRevenue(): float
    {
        // Get all active plans and count how many organizations are using them
        // For now, return 0 as revenue tracking needs to be implemented
        return 0;
    }

    /**
     * Get user growth data for the last 30 days.
     */
    private function getUserGrowthLast30Days(): array
    {
        $data = [];
        $labels = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $dayLabel = $date->format('M d');
            $labels[] = $dayLabel;

            $count = User::whereDate('created_at', $date)->count();
            $data[] = $count;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get revenue data for the last 12 months.
     */
    private function getRevenueLast12Months(): array
    {
        $data = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $startOfMonth = now()->subMonths($i)->startOfMonth();
            $endOfMonth = now()->subMonths($i)->endOfMonth();
            $monthLabel = $startOfMonth->format('M Y');
            $labels[] = $monthLabel;

            // Placeholder revenue data - implement based on your payment system
            $data[] = 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}

