<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    /**
     * Assign a plan to a user (admin assignment).
     */
    public function assignPlanToUser(User $user, Plan $plan, ?Carbon $expiresAt = null, ?string $notes = null)
    {
        return DB::transaction(function () use ($user, $plan, $expiresAt, $notes) {
            // Mark any existing active subscriptions as expired
            $this->expireUserSubscriptions($user);

            // Create new subscription record
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'source' => 'admin',
                'started_at' => now(),
                'expires_at' => $expiresAt,
                'status' => 'active',
                'notes' => $notes ?? "Plan assigned by admin",
            ]);

            // Update user's active plan
            $user->update([
                'active_plan_id' => $plan->id,
                'plan_starts_at' => now(),
            ]);

            return $subscription;
        });
    }

    /**
     * Create a subscription when user purchases a plan.
     */
    public function createPurchasedSubscription(User $user, Plan $plan, ?string $stripeSubscriptionId = null, ?float $pricePaid = null)
    {
        return DB::transaction(function () use ($user, $plan, $stripeSubscriptionId, $pricePaid) {
            // Mark any existing active subscriptions as expired
            $this->expireUserSubscriptions($user);

            // Calculate expiration date based on billing cycle
            $expiresAt = $this->calculateExpirationDate($plan);

            // Create new subscription record
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'source' => 'purchase',
                'started_at' => now(),
                'expires_at' => $expiresAt,
                'status' => 'active',
                'stripe_subscription_id' => $stripeSubscriptionId,
                'price_paid' => $pricePaid ?? $plan->price,
                'notes' => "Plan purchased by user",
            ]);

            // Update user's active plan
            $user->update([
                'active_plan_id' => $plan->id,
                'plan_starts_at' => now(),
                'stripe_subscription_id' => $stripeSubscriptionId,
            ]);

            return $subscription;
        });
    }

    /**
     * Extend user's current subscription.
     */
    public function extendSubscription(User $user, int $days)
    {
        return DB::transaction(function () use ($user, $days) {
            $subscription = Subscription::getCurrentSubscription($user);

            if (!$subscription) {
                throw new \Exception("User has no active subscription to extend");
            }

            $newExpiresAt = ($subscription->expires_at ?? now())
                ->addDays($days);

            $subscription->update([
                'expires_at' => $newExpiresAt,
            ]);

            // Also update user's trial_ends_at if on trial
            if (!$user->active_plan_id || $subscription->source === 'admin') {
                $user->update([
                    'trial_ends_at' => $newExpiresAt,
                ]);
            }

            return $subscription;
        });
    }

    /**
     * Change user's current plan to a different plan.
     */
    public function changePlan(User $user, Plan $newPlan, ?string $source = 'admin')
    {
        return DB::transaction(function () use ($user, $newPlan, $source) {
            $currentSubscription = Subscription::getCurrentSubscription($user);

            // Mark current subscription as expired
            if ($currentSubscription) {
                $currentSubscription->markAsExpired();
            }

            if ($source === 'purchase') {
                $expiresAt = $this->calculateExpirationDate($newPlan);
                return $this->createPurchasedSubscription($user, $newPlan, null, $newPlan->price);
            } else {
                return $this->assignPlanToUser($user, $newPlan, $currentSubscription?->expires_at);
            }
        });
    }

    /**
     * Cancel a subscription.
     */
    public function cancelSubscription(User $user, ?string $reason = null)
    {
        return DB::transaction(function () use ($user, $reason) {
            $subscription = Subscription::getCurrentSubscription($user);

            if (!$subscription) {
                throw new \Exception("User has no active subscription to cancel");
            }

            $subscription->update([
                'status' => 'cancelled',
                'expires_at' => now(),
                'notes' => $reason ?? "Subscription cancelled",
            ]);

            // Clear user's active plan
            $user->update([
                'active_plan_id' => null,
                'plan_starts_at' => null,
            ]);

            return $subscription;
        });
    }

    /**
     * Get user's subscription history.
     */
    public function getSubscriptionHistory(User $user)
    {
        return $user->subscriptions()
            ->with('plan')
            ->latest('started_at')
            ->get();
    }

    /**
     * Expire all active subscriptions for a user.
     */
    private function expireUserSubscriptions(User $user)
    {
        $user->subscriptions()
            ->active()
            ->each(function (Subscription $subscription) {
                $subscription->markAsExpired();
            });
    }

    /**
     * Calculate expiration date based on plan's billing cycle.
     */
    private function calculateExpirationDate(Plan $plan): Carbon
    {
        $expiresAt = now();

        return match($plan->billing_cycle) {
            'monthly' => $expiresAt->addMonth(),
            'quarterly' => $expiresAt->addMonths(3),
            'semi-annual' => $expiresAt->addMonths(6),
            'yearly' => $expiresAt->addYear(),
            default => $expiresAt->addMonth(),
        };
    }

    /**
     * Check if user has an active subscription.
     */
    public function hasActiveSubscription(User $user): bool
    {
        return $user->currentSubscription() !== null;
    }

    /**
     * Get user's current plan details.
     */
    public function getCurrentPlanDetails(User $user)
    {
        $subscription = $user->currentSubscription();

        if (!$subscription) {
            return null;
        }

        return [
            'subscription_id' => $subscription->id,
            'plan_id' => $subscription->plan_id,
            'plan_name' => $subscription->plan->name,
            'plan_price' => $subscription->plan->price,
            'billing_cycle' => $subscription->plan->billing_cycle,
            'started_at' => $subscription->started_at,
            'expires_at' => $subscription->expires_at,
            'days_remaining' => $subscription->expires_at ? $subscription->expires_at->diffInDays(now()) : null,
            'source' => $subscription->source,
            'price_paid' => $subscription->price_paid,
        ];
    }
}
