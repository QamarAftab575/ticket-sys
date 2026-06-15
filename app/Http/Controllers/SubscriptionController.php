<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Show subscription management page.
     */
    public function show(Request $request)
    {
        $user = auth()->user();
        
        // Get current subscription
        $currentSubscription = $user->currentSubscription();
        $currentPlan = $currentSubscription?->plan;
        
        // Determine tier status
        $tierStatus = null;
        $trialEndsAt = null;
        $showTrial = false;
        
        if ($user->trial_ends_at && now()->lt($user->trial_ends_at)) {
            $showTrial = true;
            $trialEndsAt = $user->trial_ends_at;
            $tierStatus = 'Trial';
        } elseif ($currentPlan) {
            $tierStatus = $currentPlan->name;
        }

        // Get subscription history
        $subscriptionHistory = $user->subscriptions()
            ->with('plan')
            ->latest('started_at')
            ->get()
            ->map(function ($subscription) {
                return [
                    'id' => $subscription->id,
                    'plan_id' => $subscription->plan_id,
                    'plan_name' => $subscription->plan->name,
                    'plan_price' => $subscription->plan->price,
                    'billing_cycle' => $subscription->plan->billing_cycle,
                    'started_at' => $subscription->started_at,
                    'expires_at' => $subscription->expires_at,
                    'status' => $subscription->status,
                    'source' => $subscription->source,
                    'is_current' => $subscription->isActive(),
                ];
            });

        // Get available plans
        $availablePlans = Plan::where('is_active', true)
            ->get()
            ->map(function ($plan) use ($currentPlan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'price' => $plan->price,
                    'billing_cycle' => $plan->billing_cycle,
                    'features' => $plan->features,
                    'is_current' => $currentPlan?->id === $plan->id,
                ];
            });

        // Get user workspaces for layout
        $userWorkspaces = $user->organizations()
            ->where('organizations.is_active', true)
            ->wherePivot('is_active', true)
            ->select('organizations.id', 'organizations.name', 'organizations.avatar_color')
            ->get();

        return inertia('Settings/Subscriptions', [
            'currentSubscription' => $currentSubscription ? [
                'id' => $currentSubscription->id,
                'plan' => [
                    'id' => $currentSubscription->plan->id,
                    'name' => $currentSubscription->plan->name,
                    'price' => $currentSubscription->plan->price,
                    'billing_cycle' => $currentSubscription->plan->billing_cycle,
                    'features' => $currentSubscription->plan->features,
                ],
                'started_at' => $currentSubscription->started_at,
                'expires_at' => $currentSubscription->expires_at,
                'days_remaining' => $currentSubscription->expires_at ? max(0, $currentSubscription->expires_at->diffInDays(now())) : null,
                'source' => $currentSubscription->source,
            ] : null,
            'subscriptionHistory' => $subscriptionHistory,
            'availablePlans' => $availablePlans,
            'userWorkspaces' => $userWorkspaces,
            'currentWorkspace' => null,
            'userRole' => 'member',
            'tierStatus' => $tierStatus,
            'trialEndsAt' => $trialEndsAt,
            'showTrial' => $showTrial,
        ]);
    }

    /**
     * Change user's plan.
     */
    public function changePlan(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $user = auth()->user();
        $plan = Plan::findOrFail($validated['plan_id']);

        $this->subscriptionService->changePlan($user, $plan, 'purchase');

        return back()->with('success', "Your subscription has been updated to {$plan->name}.");
    }

    /**
     * Cancel user's subscription.
     */
    public function cancel(Request $request)
    {
        $user = auth()->user();

        if (!$user->currentSubscription()) {
            return back()->withErrors(['error' => 'You do not have an active subscription to cancel.']);
        }

        $this->subscriptionService->cancelSubscription($user, 'Cancelled by user');

        return back()->with('success', 'Your subscription has been cancelled.');
    }

    /**
     * Rebuy the same plan (renew subscription).
     */
    public function rebuy(Request $request)
    {
        $user = auth()->user();
        $currentSubscription = $user->currentSubscription();

        if (!$currentSubscription) {
            return back()->withErrors(['error' => 'You do not have an active subscription to renew.']);
        }

        $plan = $currentSubscription->plan;

        // Create new subscription for the same plan
        $this->subscriptionService->createPurchasedSubscription(
            $user,
            $plan,
            null,
            $plan->price
        );

        return back()->with('success', "Your subscription to {$plan->name} has been renewed.");
    }
}
