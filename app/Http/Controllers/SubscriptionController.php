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
        
        // Get plan info using BillingHelper
        $planInfo = \App\Helpers\BillingHelper::getUserPlanInfo($user);
        $isOnTrial = \App\Helpers\BillingHelper::isOnTrial($user);
        $daysRemaining = \App\Helpers\BillingHelper::getDaysRemaining($user);
        
        // Get trial usage for trial users
        $trialUsage = null;
        if ($isOnTrial) {
            $trialUsage = \App\Helpers\BillingHelper::getUserTrialUsage($user);
        }
        
        // Determine tier status
        $tierStatus = $planInfo['plan_name'] ?? null;
        $trialEndsAt = $isOnTrial ? $user->trial_ends_at : null;

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
        $stripePaymentService = app(\App\Services\StripePaymentService::class);
        $availablePlans = Plan::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->map(function ($plan, $index) use ($currentPlan, $stripePaymentService) {
                $paymentInfo = $stripePaymentService->getPaymentInfo($plan);
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'description' => $plan->description,
                    'price' => $plan->price,
                    'formatted_price' => $paymentInfo['formatted_amount'],
                    'currency' => $paymentInfo['currency'],
                    'currency_sign' => $paymentInfo['currency_sign'],
                    'billing_cycle' => $plan->billing_cycle,
                    'features' => $plan->features,
                    'is_current' => $currentPlan?->id === $plan->id,
                    'highlighted' => $index === 1, // Highlight the second plan (Professional)
                ];
            });

        // Get user workspaces for layout (with role information)
        $userWorkspaces = $user->getAccessibleOrganizations()
            ->map(function ($organization) use ($user) {
                return [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'avatar_color' => $organization->avatar_color,
                    'description' => $organization->description,
                    'role' => $user->getWorkspaceRole($organization->id),
                    'is_owner' => $user->isWorkspaceOwner($organization->id),
                    'is_admin' => $user->isWorkspaceAdmin($organization->id),
                    'is_member' => $user->isWorkspaceMember($organization->id),
                ];
            });

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
            'isOnTrial' => $isOnTrial,
            'daysRemaining' => $daysRemaining,
            'trialUsage' => $trialUsage,
            'isSuperAdmin' => $user->isSuperAdmin(),
        ]);
    }

    /**
     * Get Stripe checkout session for purchasing a plan
     */
    public function getCheckoutSession(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $user = auth()->user();
        $plan = Plan::findOrFail($validated['plan_id']);

        $stripePaymentService = app(\App\Services\StripePaymentService::class);
        $result = $stripePaymentService->createCheckoutSession($user, $plan);

        return response()->json($result);
    }

    /**
     * Handle payment success callback
     * Verifies token and creates subscription
     */
    public function paymentSuccess(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        $stripePaymentService = app(\App\Services\StripePaymentService::class);
        $result = $stripePaymentService->verifyAndCreateSubscription($validated['token']);

        if (!$result['success']) {
            return redirect()->route('subscriptions.show')
                ->withErrors(['error' => $result['error']]);
        }

        return redirect()->route('subscriptions.show')
            ->with('success', "Successfully subscribed to {$result['plan_name']}! Your subscription will expire on {$result['expires_at']->format('M d, Y')}");
    }

    /**
     * Get payment intent for plan purchase
     */
    public function getPaymentIntent(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $user = auth()->user();
        $plan = Plan::findOrFail($validated['plan_id']);

        $stripePaymentService = app(\App\Services\StripePaymentService::class);
        $result = $stripePaymentService->createPaymentIntent($user, $plan);

        return response()->json($result);
    }

    /**
     * Change user's plan (with Stripe payment).
     */
    public function changePlan(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $user = auth()->user();
        $plan = Plan::findOrFail($validated['plan_id']);

        // Create checkout session for plan change
        $stripePaymentService = app(\App\Services\StripePaymentService::class);
        $result = $stripePaymentService->createCheckoutSession($user, $plan);

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['error']]);
        }

        return redirect()->away($result['checkout_url']);
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
     * Rebuy the same plan (renew subscription) with Stripe payment.
     */
    public function rebuy(Request $request)
    {
        $user = auth()->user();
        $currentSubscription = $user->currentSubscription();

        if (!$currentSubscription) {
            return back()->withErrors(['error' => 'You do not have an active subscription to renew.']);
        }

        $plan = $currentSubscription->plan;

        // Create checkout session for renewal
        $stripePaymentService = app(\App\Services\StripePaymentService::class);
        $result = $stripePaymentService->createCheckoutSession($user, $plan);

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['error']]);
        }

        return redirect()->away($result['checkout_url']);
    }
}
