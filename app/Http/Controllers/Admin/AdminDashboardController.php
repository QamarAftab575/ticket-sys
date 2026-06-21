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

        // Subscription metrics - ENHANCED
        $activeSubscriptions = \App\Models\Subscription::active()->distinct('user_id')->count('user_id');
        $expiredSubscriptions = \App\Models\Subscription::expired()->distinct('user_id')->count('user_id');
        $monthlyRevenue = $this->calculateMonthlyRevenue();
        $previousMonthRevenue = $this->calculatePreviousMonthRevenue();
        $totalRevenue = $this->calculateTotalRevenue();
        $expiredThisMonth = $this->getExpiredSubscriptionsThisMonth();
        $potentialRevenueLost = $this->calculatePotentialRevenueLost();
        $revenueGrowth = $this->calculateRevenueGrowth($monthlyRevenue, $previousMonthRevenue);

        // User distribution metrics
        $userDistribution = $this->getUserDistribution();

        // Plan performance metrics
        $planPerformance = $this->getPlanPerformance();
        $usersPerPlan = $this->getUsersPerPlan();
        $revenueByPlan = $this->getRevenueByPlan();

        // Subscription health metrics
        $subscriptionHealth = $this->getSubscriptionHealth($activeSubscriptions, $expiredSubscriptions);

        // Charts
        $userGrowth = $this->getUserGrowthLast30Days();
        $revenueAndSubscriptionTrends = $this->getRevenueAndSubscriptionTrends();
        $revenueChartData = $this->getRevenueLast12Months();

        // Recent subscription activity
        $recentActivity = $this->getRecentSubscriptionActivity();

        // Recent signups (last 10 users)
        $recentSignups = User::orderBy('created_at', 'desc')
            ->limit(10)
            ->select('id', 'name', 'email', 'created_at')
            ->get();

        return inertia('Admin/Dashboard', [
            'stats' => [
                'total_users'               => $totalUsers,
                'total_workspaces'          => $totalWorkspaces,
                'total_projects'            => $totalProjects,
                'total_tasks'               => $totalTasks,
                'new_users_today'           => $newUsersToday,
                'new_users_week'            => $newUsersWeek,
                'new_users_month'           => $newUsersMonth,
                'active_trials'             => $activeTrials,
                'expired_trials'            => $expiredTrials,
                'active_subscriptions'      => $activeSubscriptions,
                'expired_subscriptions'     => $expiredSubscriptions,
                'monthly_revenue'           => $monthlyRevenue,
                'previous_month_revenue'    => $previousMonthRevenue,
                'total_revenue'             => $totalRevenue,
                'revenue_growth'            => $revenueGrowth,
                'expired_this_month'        => $expiredThisMonth,
                'potential_revenue_lost'    => $potentialRevenueLost,
            ],
            'userDistribution'          => $userDistribution,
            'planPerformance'            => $planPerformance,
            'usersPerPlan'               => $usersPerPlan,
            'revenueByPlan'              => $revenueByPlan,
            'subscriptionHealth'         => $subscriptionHealth,
            'userGrowthChart'            => $userGrowth,
            'revenueAndSubscriptionChart' => $revenueAndSubscriptionTrends,
            'revenueChart'               => $revenueChartData,
            'recentActivity'             => $recentActivity,
            'recentSignups'              => $recentSignups,
        ]);
    }

    /**
     * Calculate monthly revenue from active subscriptions.
     */
    private function calculateMonthlyRevenue(): float
    {
        return (float) \App\Models\Subscription::where('status', 'active')
            ->whereMonth('started_at', now()->month)
            ->whereYear('started_at', now()->year)
            ->sum('price_paid');
    }

    /**
     * Calculate previous month revenue.
     */
    private function calculatePreviousMonthRevenue(): float
    {
        $lastMonth = now()->subMonth();
        return (float) \App\Models\Subscription::where('status', 'active')
            ->whereMonth('started_at', $lastMonth->month)
            ->whereYear('started_at', $lastMonth->year)
            ->sum('price_paid');
    }

    /**
     * Calculate total revenue from all subscriptions.
     */
    private function calculateTotalRevenue(): float
    {
        return (float) \App\Models\Subscription::where('status', 'active')->sum('price_paid');
    }

    /**
     * Get expired subscriptions count this month.
     */
    private function getExpiredSubscriptionsThisMonth(): int
    {
        return \App\Models\Subscription::expired()
            ->whereMonth('expires_at', now()->month)
            ->whereYear('expires_at', now()->year)
            ->count();
    }

    /**
     * Calculate potential revenue lost from expired subscriptions.
     */
    private function calculatePotentialRevenueLost(): float
    {
        return (float) \App\Models\Subscription::expired()
            ->whereMonth('expires_at', now()->month)
            ->whereYear('expires_at', now()->year)
            ->sum('price_paid');
    }

    /**
     * Calculate revenue growth percentage.
     */
    private function calculateRevenueGrowth(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Get user distribution (Trial, Active, Expired).
     */
    private function getUserDistribution(): array
    {
        $trialUsers = User::where('trial_ends_at', '>', now())->count();
        $activeSubscribers = \App\Models\Subscription::active()->distinct('user_id')->count('user_id');
        $expiredSubscribers = \App\Models\Subscription::expired()->distinct('user_id')->count('user_id');

        $total = $trialUsers + $activeSubscribers + $expiredSubscribers;

        return [
            'labels' => ['Trial Users', 'Active Subscribers', 'Expired Subscribers'],
            'data' => [$trialUsers, $activeSubscribers, $expiredSubscribers],
            'percentages' => [
                round(($trialUsers / max($total, 1)) * 100, 1),
                round(($activeSubscribers / max($total, 1)) * 100, 1),
                round(($expiredSubscribers / max($total, 1)) * 100, 1),
            ],
            'backgroundColor' => ['#3B82F6', '#10B981', '#F97316'],
            'borderColor' => ['#1E40AF', '#059669', '#EA580C'],
        ];
    }

    /**
     * Get subscription health metrics.
     */
    private function getSubscriptionHealth(int $active, int $expired): array
    {
        $totalSubscriptions = $active + $expired;
        $churnRate = $totalSubscriptions > 0 ? round(($expired / $totalSubscriptions) * 100, 2) : 0;
        $conversionRate = round((($active / max(User::count(), 1)) * 100), 2);
        $totalRevenue = $this->calculateTotalRevenue();
        $activeUserCount = \App\Models\Subscription::active()->distinct('user_id')->count('user_id');
        $arpu = $activeUserCount > 0 ? round($totalRevenue / $activeUserCount, 2) : 0;

        return [
            'active_subscriptions' => $active,
            'expired_subscriptions' => $expired,
            'conversion_rate' => $conversionRate,
            'churn_rate' => $churnRate,
            'monthly_revenue' => $this->calculateMonthlyRevenue(),
            'arpu' => $arpu,
        ];
    }

    /**
     * Get plan performance data.
     */
    private function getPlanPerformance(): array
    {
        $plans = Plan::where('is_active', true)
            ->with(['subscriptions' => function ($query) {
                $query->where('status', 'active');
            }])
            ->get();

        return $plans->map(function ($plan) {
            $subscriptionCount = \App\Models\Subscription::where('plan_id', $plan->id)
                ->where('status', 'active')
                ->count();
            
            $revenue = \App\Models\Subscription::where('plan_id', $plan->id)
                ->where('status', 'active')
                ->sum('price_paid');

            return [
                'name' => $plan->name,
                'users' => $subscriptionCount,
                'revenue' => (float) $revenue,
                'price' => (float) $plan->price,
            ];
        })->sortByDesc('users')->values()->toArray();
    }

    /**
     * Get users per plan breakdown.
     */
    private function getUsersPerPlan(): array
    {
        $planData = \App\Models\Subscription::active()
            ->select('plan_id', DB::raw('count(*) as user_count'))
            ->groupBy('plan_id')
            ->with('plan')
            ->get();

        $labels = [];
        $data = [];

        foreach ($planData as $item) {
            if ($item->plan) {
                $labels[] = $item->plan->name;
                $data[] = $item->user_count;
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'backgroundColor' => ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'],
            'borderColor' => ['#1E40AF', '#059669', '#D97706', '#DC2626', '#6D28D9'],
        ];
    }

    /**
     * Get revenue by plan breakdown.
     */
    private function getRevenueByPlan(): array
    {
        $revenueData = \App\Models\Subscription::active()
            ->select('plan_id', DB::raw('sum(price_paid) as total_revenue'))
            ->groupBy('plan_id')
            ->with('plan')
            ->get();

        $labels = [];
        $data = [];

        foreach ($revenueData as $item) {
            if ($item->plan) {
                $labels[] = $item->plan->name;
                $data[] = (float) $item->total_revenue;
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'backgroundColor' => ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'],
            'borderColor' => ['#1E40AF', '#059669', '#D97706', '#DC2626', '#6D28D9'],
        ];
    }

    /**
     * Get revenue and subscription trends for the last 12 months.
     */
    private function getRevenueAndSubscriptionTrends(): array
    {
        $data = [];
        $labels = [];
        $activeSubscriptionCounts = [];
        $expiredSubscriptionCounts = [];
        $revenueCounts = [];

        for ($i = 11; $i >= 0; $i--) {
            $startOfMonth = now()->subMonths($i)->startOfMonth();
            $endOfMonth = now()->subMonths($i)->endOfMonth();
            $monthLabel = $startOfMonth->format('M Y');
            $labels[] = $monthLabel;

            // Active subscriptions for this month
            $activeCount = \App\Models\Subscription::where('status', 'active')
                ->whereBetween('started_at', [$startOfMonth, $endOfMonth])
                ->distinct('user_id')
                ->count('user_id');
            $activeSubscriptionCounts[] = $activeCount;

            // Expired subscriptions for this month
            $expiredCount = \App\Models\Subscription::expired()
                ->whereBetween('expires_at', [$startOfMonth, $endOfMonth])
                ->distinct('user_id')
                ->count('user_id');
            $expiredSubscriptionCounts[] = $expiredCount;

            // Revenue for this month
            $revenue = \App\Models\Subscription::where('status', 'active')
                ->whereBetween('started_at', [$startOfMonth, $endOfMonth])
                ->sum('price_paid');
            $revenueCounts[] = (float) $revenue;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Active Subscriptions',
                    'data' => $activeSubscriptionCounts,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.05)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 4,
                    'pointBackgroundColor' => '#10B981',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                ],
                [
                    'label' => 'Expired Subscriptions',
                    'data' => $expiredSubscriptionCounts,
                    'borderColor' => '#F97316',
                    'backgroundColor' => 'rgba(249, 115, 22, 0.05)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 4,
                    'pointBackgroundColor' => '#F97316',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                ],
                [
                    'label' => 'Monthly Revenue',
                    'data' => $revenueCounts,
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.05)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 4,
                    'pointBackgroundColor' => '#3B82F6',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                ],
            ],
        ];
    }

    /**
     * Get recent subscription activity.
     */
    private function getRecentSubscriptionActivity(): array
    {
        $activities = \App\Models\Subscription::with('user', 'plan')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($subscription) {
                $type = $subscription->status === 'active' ? 'activated' : 'expired';
                
                return [
                    'id' => $subscription->id,
                    'user_name' => $subscription->user->name,
                    'user_email' => $subscription->user->email,
                    'plan_name' => $subscription->plan->name,
                    'type' => $type,
                    'amount' => (float) $subscription->price_paid,
                    'created_at' => $subscription->created_at,
                ];
            })->toArray();

        return $activities;
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

            // Get revenue for this month from subscriptions
            $revenue = \App\Models\Subscription::where('status', 'active')
                ->whereBetween('started_at', [$startOfMonth, $endOfMonth])
                ->sum('price_paid');
            
            $data[] = (float) $revenue;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
