# Complete Subscription Flow Documentation

## Overview
This document outlines the complete subscription flow for Asira, including all edge cases and security measures.

---

## 1. SUBSCRIPTION PURCHASE FLOW

### 1.1 User Initiates Purchase
**Flow:** User clicks "Upgrade to [Plan]" button on subscriptions page

```
Frontend Action
    ↓
POST /subscription/checkout-session
    ↓
SubscriptionController::getCheckoutSession()
    ↓
StripePaymentService::createCheckoutSession()
    ├── Create PaymentToken with unique token
    ├── Generate Stripe Checkout Session
    ├── Store session ID in PaymentToken
    └── Return checkout_url
    ↓
Redirect to Stripe Checkout URL
```

### 1.2 Payment Processing
- User completes payment on Stripe
- Stripe redirects to success URL with payment token

### 1.3 Payment Verification & Subscription Creation
**Flow:** Success URL callback with token parameter

```
GET /subscription/payment-success?token={unique_token}
    ↓
SubscriptionController::paymentSuccess()
    ↓
StripePaymentService::verifyAndCreateSubscription()
    ↓
PaymentVerificationService::verifyPayment()
    │
    ├─ SECURITY CHECK 1: Token format validation (SHA256 hash)
    ├─ SECURITY CHECK 2: Token exists in database
    ├─ SECURITY CHECK 3: Token not expired (24 hours)
    ├─ SECURITY CHECK 4: Token not already used
    ├─ SECURITY CHECK 5: User ownership verification
    ├─ SECURITY CHECK 6: Stripe session exists
    ├─ SECURITY CHECK 7: Verify Stripe payment_status = 'paid'
    ├─ SECURITY CHECK 8: Amount verification
    └─ SECURITY CHECK 9: HMAC signature verification
    ↓
    All Checks Pass ✓
    ↓
PaymentVerificationService::createSubscriptionFromPayment()
    ├── Expire previous active subscriptions
    ├── Create new Subscription record with:
    │   ├── payment_intent_id (from Stripe session)
    │   ├── payment_token_id (reference to PaymentToken)
    │   ├── price_paid (verified amount)
    │   ├── payment_metadata (full transaction details)
    │   └── payment_attempts (1)
    ├── Update User.active_plan_id
    ├── Mark PaymentToken as 'used'
    └── Log successful payment
    ↓
Redirect to /settings/subscriptions
    ↓
Display Success Message
```

---

## 2. SUBSCRIPTION RENEWAL FLOW

### 2.1 User Initiates Renewal
**Flow:** User clicks "Renew Plan" button

```
Frontend Action
    ↓
POST /settings/subscriptions/rebuy
    ↓
SubscriptionController::rebuy()
    ├── Get current subscription
    ├── Verify subscription exists and is active
    ├── Get plan from current subscription
    └── Create checkout session (same as purchase)
    ↓
Redirect to Stripe Checkout URL
    ↓
(Same payment verification flow as 1.3)
    ↓
New Subscription created with:
├── expires_at = now() + billing_cycle
├── Previous subscription marked as 'expired'
└── User.active_plan_id updated
```

**Database State After Renewal:**
```
Old Subscription: status='expired', expires_at=now()
New Subscription: status='active', expires_at=now()+1month
User.active_plan_id = new_plan_id
```

---

## 3. PLAN CHANGE/UPGRADE FLOW

### 3.1 User Initiates Plan Change
**Flow:** User clicks "Upgrade to [Different Plan]" button

```
Frontend Action
    ↓
POST /settings/subscriptions/change-plan
    ↓
SubscriptionController::changePlan()
    ├── Get current plan from user.active_plan_id
    ├── Get new plan from request
    ├── Validate plans are different
    └── Create checkout session for new plan
    ↓
Redirect to Stripe Checkout URL
    ↓
(Same payment verification flow as 1.3)
    ↓
New Subscription created
├── Old subscription marked as 'expired'
├── New subscription linked to new plan
└── All billing dates reset
```

**Database State After Plan Change:**
```
Old Subscription: 
  ├── plan_id = old_plan
  ├── status = 'expired'
  └── expires_at = now()

New Subscription:
  ├── plan_id = new_plan
  ├── status = 'active'
  ├── started_at = now()
  ├── expires_at = now() + new_billing_cycle
  └── price_paid = new_plan_price
```

---

## 4. SUBSCRIPTION CANCELLATION FLOW

### 4.1 User Cancels Subscription
**Flow:** User clicks "Cancel Subscription" button

```
Frontend Action
    ↓
POST /settings/subscriptions/cancel
    ↓
SubscriptionController::cancel()
    ├── Get current subscription
    ├── Verify subscription exists
    └── Call SubscriptionService::cancelSubscription()
    ↓
SubscriptionService::cancelSubscription()
    ├── Update subscription:
    │   ├── status = 'cancelled'
    │   ├── expires_at = now()
    │   └── notes = "Cancelled by user"
    ├── Clear User.active_plan_id
    └── Log cancellation
    ↓
Database Update Complete
```

**Database State After Cancellation:**
```
Subscription:
  ├── status = 'cancelled'
  ├── expires_at = now()
  └── User can no longer use premium features

User:
  ├── active_plan_id = null
  └── access_level = basic (free tier)
```

---

## 5. PAYMENT TOKEN LIFECYCLE

### 5.1 Token States
```
PENDING   → Created when checkout session starts
         → Valid for 24 hours
         → Expires automatically after 24 hours
         ↓
USED      → Marked after successful subscription creation
         → Cannot be reused (prevents duplicate charges)
         ↓
EXPIRED   → Token older than 24 hours (manual marking possible)
         → System rejects these tokens
```

### 5.2 Edge Cases Handled

**Case 1: User Clicks Success URL Multiple Times**
- First click: Token status = 'pending' ✓ Subscription created
- Second click: Token status = 'used' ✗ Error: "Token already used"
- Result: Single subscription created, no duplicate charges

**Case 2: User Abandons Checkout**
- Token created but never paid
- After 24 hours: Token auto-expires
- No subscription created
- User can initiate new checkout with new token

**Case 3: Browser Crashes on Success Page**
- Success URL not loaded
- PaymentToken remains in 'pending' state for 24 hours
- User can retry with same token OR create new checkout
- First successful token verification wins

**Case 4: Network Delay on Success URL**
- Payment completed in Stripe
- Success URL callback delayed
- When callback arrives (even after hours): Token still valid
- Subscription created successfully

---

## 6. SECURITY FEATURES

### 6.1 Multi-Layer Verification
```
Layer 1: Token Format     → SHA256 hash (64 hex chars)
Layer 2: Token Existence  → Must exist in payment_tokens table
Layer 3: Token Expiry     → Must be within 24 hours
Layer 4: Token Status     → Must be 'pending'
Layer 5: User Ownership   → Token.user_id must match auth user
Layer 6: Stripe Session   → Session must exist and be paid
Layer 7: Amount Match     → Stripe amount must match token amount
Layer 8: HMAC Signature   → Cryptographic verification
```

### 6.2 Payment Metadata Storage
Every subscription stores:
```json
{
  "payment_metadata": {
    "stripe_session_id": "cs_test_...",
    "stripe_payment_intent_id": "pi_test_...",
    "customer_email": "user@example.com",
    "currency": "usd",
    "verified_at": "2026-06-20T12:00:00Z",
    "verification_method": "token_based_verification"
  }
}
```

This allows complete audit trail for any payment verification.

---

## 7. DATA STRUCTURES

### 7.1 PaymentToken Model
```php
PaymentToken {
  id: UUID,
  user_id: UUID,
  plan_id: UUID,
  token: string (unique SHA256),
  stripe_session_id: string,
  status: enum('pending', 'used', 'expired'),
  amount: decimal,
  currency: string,
  expires_at: timestamp,
  used_at: timestamp (null until used),
  created_at: timestamp
}
```

### 7.2 Subscription Model (Enhanced)
```php
Subscription {
  id: UUID,
  user_id: UUID,
  plan_id: UUID,
  stripe_subscription_id: string (session ID),
  payment_intent_id: string (from Stripe),
  payment_token_id: UUID (reference),
  price_paid: decimal,
  payment_metadata: json,
  payment_attempts: integer,
  status: enum('active', 'expired', 'cancelled'),
  started_at: timestamp,
  expires_at: timestamp,
  source: enum('admin', 'purchase'),
  created_at: timestamp
}
```

---

## 8. AUDIT LOGGING

All payment actions logged to `storage/logs/payments.log`:
```
[timestamp] Payment attempt by user_id for plan_id
  status: success/failed
  error: if any
  payment_token_id: reference
  stripe_session_id: if applicable
```

---

## 9. ERROR HANDLING & RECOVERY

### 9.1 Common Errors

| Scenario | Error | Recovery |
|----------|-------|----------|
| User closes payment page | No token created | User starts new checkout |
| Payment fails on Stripe | Token remains pending | User can retry with same token |
| Network error on success page | Subscription pending | Retry success URL with same token |
| Token expires (24h) | Token expired | Create new checkout session |
| Double-click success URL | Token already used | Show "already subscribed" message |
| Wrong user accesses token | User mismatch | 403 Forbidden |
| Stripe session invalid | Session not found | Contact support |

### 9.2 Automatic Cleanup

```php
// Scheduled job (can be added)
php artisan schedule:run

// Mark expired tokens
PaymentToken::where('expires_at', '<', now())
  ->where('status', 'pending')
  ->update(['status' => 'expired']);
```

---

## 10. TESTING CHECKLIST

### Scenario 1: New Subscription Purchase
- [ ] User clicks "Upgrade to Professional"
- [ ] Redirected to Stripe Checkout
- [ ] Complete payment with test card
- [ ] Redirected to success page
- [ ] Subscription created in database
- [ ] Plan shows as "Current Plan"
- [ ] Expiration date calculated correctly

### Scenario 2: Plan Renewal
- [ ] User clicks "Renew Plan"
- [ ] Redirected to Stripe Checkout
- [ ] Complete payment
- [ ] New subscription created
- [ ] Old subscription marked as expired
- [ ] Expiration date extended

### Scenario 3: Plan Upgrade
- [ ] User on Professional plan
- [ ] User clicks "Upgrade to Business"
- [ ] Redirected to Stripe Checkout
- [ ] Complete payment
- [ ] Old subscription expired
- [ ] New subscription with Business plan
- [ ] New expiration date

### Scenario 4: Plan Downgrade
- [ ] User on Business plan
- [ ] User clicks "Downgrade to Professional"
- [ ] Redirected to Stripe Checkout
- [ ] Complete payment (prorated if needed)
- [ ] Old subscription expired
- [ ] New subscription with Professional plan

### Scenario 5: Subscription Cancellation
- [ ] User clicks "Cancel Subscription"
- [ ] Confirmation modal shown
- [ ] User confirms cancellation
- [ ] Subscription marked as cancelled
- [ ] User reverts to free tier
- [ ] Cannot access premium features

### Scenario 6: Double-Click Success URL
- [ ] Payment completed
- [ ] Success URL received
- [ ] Subscription created
- [ ] User refreshes/clicks back
- [ ] System shows "already subscribed"
- [ ] No duplicate subscription

### Scenario 7: Token Expiration
- [ ] Create payment token
- [ ] Wait 24+ hours
- [ ] Try to access with expired token
- [ ] System rejects: "Token expired"
- [ ] User must create new checkout

### Scenario 8: Network Failure on Success
- [ ] Payment completed in Stripe
- [ ] Success URL endpoint fails (simulate)
- [ ] Try success URL again after recovery
- [ ] Subscription created successfully

---

## 11. CONFIGURATION

### Environment Variables
```env
SITE_CURRENCY=usd
SITE_CURRENCY_sign=$
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...
```

### Routes
```
POST   /subscription/checkout-session      → getCheckoutSession()
GET    /subscription/payment-success       → paymentSuccess()
POST   /settings/subscriptions/change-plan → changePlan()
POST   /settings/subscriptions/rebuy       → rebuy()
POST   /settings/subscriptions/cancel      → cancel()
GET    /settings/subscriptions             → show()
```

---

## 12. PERFORMANCE CONSIDERATIONS

- Payment tokens auto-expire after 24 hours (configurable)
- Subscription history indexed by user_id and status
- Payment verification happens synchronously (< 500ms)
- Minimal database queries (2-3 queries per verification)
- Logs rotated daily, kept for 90 days

---

## 13. MONITORING

Monitor these metrics:
```
- Payment success rate
- Token expiration rate
- Average verification time
- Error rate by error type
- Duplicate subscription attempts
```

---

## Conclusion

This subscription system provides:
✅ Secure token-based verification (no webhooks needed)
✅ Complete audit trail with payment metadata
✅ Handles all edge cases gracefully
✅ Prevents duplicate charges
✅ Smooth user experience
✅ Easy troubleshooting with logs
✅ Scalable architecture
