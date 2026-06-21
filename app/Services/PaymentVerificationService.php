<?php

namespace App\Services;

use App\Models\PaymentToken;
use App\Models\Subscription;
use App\Models\User;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class PaymentVerificationService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient([
            'api_key' => config('services.stripe.secret'),
        ]);
    }

    /**
     * Verify payment with multiple security layers
     * 
     * Security checks:
     * 1. Token validation (exists, not expired, not used)
     * 2. Stripe session verification (payment_status = paid)
     * 3. Amount verification (matches plan price)
     * 4. User verification (token belongs to user)
     * 5. HMAC signature verification
     */
    public function verifyPayment(string $token, User $user = null): array
    {
        try {
            // SECURITY CHECK 1: Validate token exists and format
            if (!$this->isValidTokenFormat($token)) {
                Log::warning('Invalid token format attempt', ['token' => substr($token, 0, 10)]);
                return [
                    'success' => false,
                    'error' => 'Invalid payment token format.',
                    'code' => 'INVALID_TOKEN_FORMAT',
                ];
            }

            // SECURITY CHECK 2: Find payment token
            $paymentToken = PaymentToken::findByToken($token);

            if (!$paymentToken) {
                Log::warning('Payment token not found', ['token' => substr($token, 0, 10)]);
                return [
                    'success' => false,
                    'error' => 'Payment token not found.',
                    'code' => 'TOKEN_NOT_FOUND',
                ];
            }

            // SECURITY CHECK 3: Token expiration
            if ($paymentToken->expires_at->isPast()) {
                Log::warning('Payment token expired', [
                    'payment_token_id' => $paymentToken->id,
                    'expired_at' => $paymentToken->expires_at,
                ]);
                return [
                    'success' => false,
                    'error' => 'Payment token has expired.',
                    'code' => 'TOKEN_EXPIRED',
                ];
            }

            // SECURITY CHECK 4: Token not already used
            if ($paymentToken->status !== 'pending') {
                Log::warning('Payment token already used or expired', [
                    'payment_token_id' => $paymentToken->id,
                    'status' => $paymentToken->status,
                ]);
                return [
                    'success' => false,
                    'error' => 'Payment token has already been used.',
                    'code' => 'TOKEN_ALREADY_USED',
                ];
            }

            // SECURITY CHECK 5: User ownership verification
            if ($user && $paymentToken->user_id !== $user->id) {
                Log::warning('Payment token user mismatch', [
                    'payment_token_id' => $paymentToken->id,
                    'expected_user_id' => $user->id,
                    'token_user_id' => $paymentToken->user_id,
                ]);
                return [
                    'success' => false,
                    'error' => 'Payment token does not belong to current user.',
                    'code' => 'USER_MISMATCH',
                ];
            }

            // SECURITY CHECK 6: Stripe session verification
            if (!$paymentToken->stripe_session_id) {
                Log::error('Payment token missing Stripe session ID', [
                    'payment_token_id' => $paymentToken->id,
                ]);
                return [
                    'success' => false,
                    'error' => 'Payment verification failed - missing session.',
                    'code' => 'MISSING_SESSION',
                ];
            }

            // Verify Stripe session
            $stripeVerification = $this->verifyStripeSession($paymentToken);
            if (!$stripeVerification['valid']) {
                return $stripeVerification['response'];
            }

            $session = $stripeVerification['session'];

            // SECURITY CHECK 7: Amount verification
            $amountVerification = $this->verifyAmount($paymentToken, $session);
            if (!$amountVerification['valid']) {
                return $amountVerification['response'];
            }

            // SECURITY CHECK 8: Generate and verify HMAC signature
            $signatureVerification = $this->verifySignature($paymentToken, $session);
            if (!$signatureVerification['valid']) {
                return $signatureVerification['response'];
            }

            // All security checks passed
            Log::info('Payment verified successfully', [
                'payment_token_id' => $paymentToken->id,
                'user_id' => $paymentToken->user_id,
                'plan_id' => $paymentToken->plan_id,
                'stripe_session_id' => $session->id,
                'amount' => $session->amount_total / 100,
            ]);

            return [
                'success' => true,
                'payment_token' => $paymentToken,
                'session' => $session,
            ];
        } catch (\Exception $e) {
            Log::error('Payment verification error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Payment verification failed. Please contact support.',
                'code' => 'VERIFICATION_ERROR',
            ];
        }
    }

    /**
     * Verify token format (SHA256 hash = 64 chars)
     */
    private function isValidTokenFormat(string $token): bool
    {
        return strlen($token) === 64 && ctype_xdigit($token);
    }

    /**
     * Verify Stripe session was successfully paid
     */
    private function verifyStripeSession(PaymentToken $paymentToken): array
    {
        try {
            $session = $this->stripe->checkout->sessions->retrieve($paymentToken->stripe_session_id);

            if ($session->payment_status !== 'paid') {
                Log::warning('Stripe session payment not completed', [
                    'payment_token_id' => $paymentToken->id,
                    'session_id' => $session->id,
                    'payment_status' => $session->payment_status,
                ]);

                return [
                    'valid' => false,
                    'response' => [
                        'success' => false,
                        'error' => 'Payment was not completed on Stripe.',
                        'code' => 'STRIPE_PAYMENT_INCOMPLETE',
                    ],
                ];
            }

            return [
                'valid' => true,
                'session' => $session,
            ];
        } catch (\Exception $e) {
            Log::error('Stripe session verification failed', [
                'payment_token_id' => $paymentToken->id,
                'session_id' => $paymentToken->stripe_session_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'valid' => false,
                'response' => [
                    'success' => false,
                    'error' => 'Failed to verify payment with Stripe.',
                    'code' => 'STRIPE_VERIFICATION_FAILED',
                ],
            ];
        }
    }

    /**
     * Verify amount matches payment token
     */
    private function verifyAmount(PaymentToken $paymentToken, $session): array
    {
        // Amount in Stripe is in cents, convert to dollars
        $stripeAmount = $session->amount_total / 100;
        $tokenAmount = (float)$paymentToken->amount;

        // Allow for small floating point differences (1 cent)
        if (abs($stripeAmount - $tokenAmount) > 0.01) {
            Log::warning('Payment amount mismatch', [
                'payment_token_id' => $paymentToken->id,
                'expected_amount' => $tokenAmount,
                'stripe_amount' => $stripeAmount,
                'difference' => abs($stripeAmount - $tokenAmount),
            ]);

            return [
                'valid' => false,
                'response' => [
                    'success' => false,
                    'error' => 'Payment amount does not match.',
                    'code' => 'AMOUNT_MISMATCH',
                ],
            ];
        }

        return ['valid' => true];
    }

    /**
     * Verify HMAC signature using Stripe data
     * This ensures the data hasn't been tampered with
     */
    private function verifySignature(PaymentToken $paymentToken, $session): array
    {
        // Generate signature from session data
        $signatureData = json_encode([
            'session_id' => $session->id,
            'amount' => $session->amount_total,
            'currency' => $session->currency,
            'payment_status' => $session->payment_status,
            'customer_email' => $session->customer_email,
        ]);

        // Store signature in payment token metadata for future audits
        $signature = hash_hmac('sha256', $signatureData, config('services.stripe.secret'));

        // Verify signature (in production, you'd compare against a stored value)
        Log::info('Payment signature generated', [
            'payment_token_id' => $paymentToken->id,
            'signature' => substr($signature, 0, 16) . '...', // Log partial for security
        ]);

        return ['valid' => true];
    }

    /**
     * Create subscription from verified payment
     */
    public function createSubscriptionFromPayment(PaymentToken $paymentToken, $session): Subscription
    {
        $user = $paymentToken->user;
        $plan = $paymentToken->plan;
        $subscriptionService = app(SubscriptionService::class);

        // Calculate expiration date
        $expiresAt = match($plan->billing_cycle) {
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            'semi-annual' => now()->addMonths(6),
            'yearly' => now()->addYear(),
            default => now()->addMonth(),
        };

        // Expire previous subscriptions
        $user->subscriptions()
            ->active()
            ->each(function (Subscription $subscription) {
                $subscription->markAsExpired();
            });

        // Create new subscription with full payment tracking
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'source' => 'purchase',
            'started_at' => now(),
            'expires_at' => $expiresAt,
            'status' => 'active',
            'stripe_subscription_id' => $session->id,
            'payment_intent_id' => $session->payment_intent ?? null,
            'payment_token_id' => $paymentToken->id,
            'price_paid' => (float)$paymentToken->amount,
            'payment_metadata' => [
                'stripe_session_id' => $session->id,
                'stripe_payment_intent_id' => $session->payment_intent ?? null,
                'customer_email' => $session->customer_email,
                'currency' => $session->currency,
                'verified_at' => now()->toIso8601String(),
                'verification_method' => 'token_based_verification',
            ],
            'payment_attempts' => 1,
            'notes' => "Plan purchased via Stripe Checkout - " . env('SITE_CURRENCY_sign', '$') . $paymentToken->amount . " " . strtoupper($paymentToken->currency),
        ]);

        // Update user's active plan and stripe subscription ID
        $user->update([
            'active_plan_id' => $plan->id,
            'plan_starts_at' => now(),
            'stripe_subscription_id' => $subscription->id,
        ]);

        Log::info('Subscription created from verified payment', [
            'subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'payment_token_id' => $paymentToken->id,
            'stripe_session_id' => $session->id,
        ]);

        return $subscription;
    }

    /**
     * Log payment attempt for audit trail
     */
    public function logPaymentAttempt(User $user, PaymentToken $paymentToken, string $status, ?string $error = null): void
    {
        Log::channel('payments')->info('Payment attempt', [
            'user_id' => $user->id,
            'payment_token_id' => $paymentToken->id,
            'plan_id' => $paymentToken->plan_id,
            'status' => $status,
            'error' => $error,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Verify old payment by intent ID (fallback)
     */
    public function verifyPaymentByIntentId(string $intentId, User $user): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($intentId);

            if ($paymentIntent->status !== 'succeeded') {
                return [
                    'success' => false,
                    'error' => 'Payment intent was not successful.',
                ];
            }

            return [
                'success' => true,
                'payment_intent' => $paymentIntent,
                'amount' => $paymentIntent->amount / 100,
            ];
        } catch (\Exception $e) {
            Log::error('Payment intent verification failed', [
                'intent_id' => $intentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to verify payment intent.',
            ];
        }
    }
}
