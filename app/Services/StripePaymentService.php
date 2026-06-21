<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\PaymentToken;
use App\Models\Subscription;
use App\Models\User;
use Stripe\Stripe;
use Stripe\StripeClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StripePaymentService
{
    private StripeClient $stripe;
    private string $currency;
    private string $currencySign;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->stripe = new StripeClient([
            'api_key' => config('services.stripe.secret'),
        ]);
        
        // Get currency from environment
        $this->currency = strtolower(env('SITE_CURRENCY', 'usd'));
        $this->currencySign = env('SITE_CURRENCY_SIGN', '$');
    }

    /**
     * Create Stripe Checkout session and payment token
     * Returns checkout URL
     */
    public function createCheckoutSession(User $user, Plan $plan): array
    {
        try {
            // Create payment token for verification
            $paymentToken = PaymentToken::createForPayment($user, $plan, $this->currency);

            // Amount in cents
            $amount = (int)($plan->price * 100);

            // Build success and cancel URLs with token
            $baseUrl = config('app.url');
            $successUrl = "{$baseUrl}/subscription/payment-success?token={$paymentToken->token}";
            $cancelUrl = "{$baseUrl}/settings/subscriptions";

            // Create Stripe Checkout Session
            $session = $this->stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'mode' => 'payment',
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => $this->currency,
                            'product_data' => [
                                'name' => $plan->name,
                                'description' => $plan->description,
                                'metadata' => [
                                    'plan_id' => $plan->id,
                                    'plan_name' => $plan->name,
                                ],
                            ],
                            'unit_amount' => $amount,
                        ],
                        'quantity' => 1,
                    ],
                ],
                'metadata' => [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'payment_token_id' => $paymentToken->id,
                ],
                'customer_email' => $user->email,
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);

            // Store Stripe session ID in payment token
            $paymentToken->update(['stripe_session_id' => $session->id]);

            return [
                'success' => true,
                'checkout_url' => $session->url,
                'session_id' => $session->id,
                'token' => $paymentToken->token,
            ];
        } catch (\Exception $e) {
            Log::error('Stripe checkout session creation failed', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to create checkout session.',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment token and create subscription
     * Called from success URL
     */
    public function verifyAndCreateSubscription(string $token): array
    {
        $verificationService = app(\App\Services\PaymentVerificationService::class);

        // Verify payment with all security checks
        $verification = $verificationService->verifyPayment($token);

        if (!$verification['success']) {
            return $verification;
        }

        $paymentToken = $verification['payment_token'];
        $session = $verification['session'];

        try {
            // Create subscription from verified payment
            $subscription = $verificationService->createSubscriptionFromPayment($paymentToken, $session);

            // Mark payment token as used
            $paymentToken->markAsUsed();

            // Log successful payment
            $verificationService->logPaymentAttempt(
                $paymentToken->user,
                $paymentToken,
                'success'
            );

            return [
                'success' => true,
                'subscription_id' => $subscription->id,
                'message' => "Successfully subscribed to {$paymentToken->plan->name}",
                'plan_name' => $paymentToken->plan->name,
                'plan_price' => $paymentToken->amount,
                'currency' => strtoupper($paymentToken->currency),
                'payment_intent_id' => $session->payment_intent ?? null,
                'expires_at' => $subscription->expires_at,
            ];
        } catch (\Exception $e) {
            Log::error('Subscription creation from payment failed', [
                'payment_token_id' => $paymentToken->id,
                'error' => $e->getMessage(),
            ]);

            // Log failed payment
            $verificationService->logPaymentAttempt(
                $paymentToken->user,
                $paymentToken,
                'failed',
                $e->getMessage()
            );

            return [
                'success' => false,
                'error' => 'Failed to create subscription. Please contact support.',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create database subscription record
     */
    private function createDatabaseSubscription(User $user, Plan $plan, string $stripeReference, float $pricePaid): Subscription
    {
        // Expire previous active subscriptions
        $user->subscriptions()
            ->active()
            ->each(function (Subscription $subscription) {
                $subscription->markAsExpired();
            });

        // Calculate expiration date
        $expiresAt = $this->calculateExpirationDate($plan);

        // Create new subscription
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'source' => 'purchase',
            'started_at' => now(),
            'expires_at' => $expiresAt,
            'status' => 'active',
            'stripe_subscription_id' => $stripeReference,
            'price_paid' => $pricePaid,
            'notes' => "Plan purchased via Stripe Checkout - {$this->currencySign}{$pricePaid} {$this->currency}",
        ]);

        // Update user's active plan
        $user->update([
            'active_plan_id' => $plan->id,
            'plan_starts_at' => now(),
        ]);

        Log::info('Subscription created from payment', [
            'subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        return $subscription;
    }

    /**
     * Calculate expiration date based on billing cycle
     */
    private function calculateExpirationDate(Plan $plan)
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
     * Get payment info formatted for display
     */
    public function getPaymentInfo(Plan $plan): array
    {
        return [
            'plan_name' => $plan->name,
            'amount' => $plan->price,
            'currency' => strtoupper($this->currency),
            'currency_sign' => $this->currencySign,
            'formatted_amount' => "{$this->currencySign}{$plan->price}",
            'billing_cycle' => $plan->billing_cycle,
            'description' => $plan->description,
        ];
    }

    /**
     * Get currency and sign
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getCurrencySign(): string
    {
        return $this->currencySign;
    }
}
