# Subscription System Architecture

## System Overview Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        USER INTERFACE                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ADMIN PATH                              USER PATH               │
│  /admin/users/{id}                       /settings/subscriptions │
│  └─ Assign Plan button        ──────→   └─ Current Plan Card     │
│  └─ Extend Trial button       ──────→   └─ Available Plans       │
│  └─ Subscription History      ◄───────  └─ Change Plan Modal     │
│                                         └─ Renew Modal           │
│                                         └─ Cancel Modal          │
│                                         └─ History Table         │
│                                                                   │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                    HTTP Requests
                           │
          ┌────────────────┴────────────────┐
          │                                 │
┌─────────▼──────────────────┐   ┌─────────▼──────────────────┐
│ SubscriptionController     │   │ AdminUserController        │
├────────────────────────────┤   ├────────────────────────────┤
│ show()                     │   │ assignPlan()               │
│ changePlan()               │   │ extendTrial()              │
│ rebuy()                    │   │                            │
│ cancel()                   │   │ (uses SubscriptionService) │
└────────────┬───────────────┘   └────────────┬───────────────┘
             │                                │
             └────────────┬───────────────────┘
                          │
                   Service Layer
                          │
          ┌───────────────▼───────────────┐
          │  SubscriptionService          │
          ├───────────────────────────────┤
          │ assignPlanToUser()            │
          │ createPurchasedSubscription() │
          │ changePlan()                  │
          │ extendSubscription()          │
          │ cancelSubscription()          │
          │ getSubscriptionHistory()      │
          │ getCurrentPlanDetails()       │
          │ hasActiveSubscription()       │
          └───────────────┬───────────────┘
                          │
                      Database
                          │
        ┌─────────────────┴─────────────────┐
        │                                   │
┌───────▼─────────────┐         ┌──────────▼────────┐
│  subscriptions      │         │  users (modified) │
├─────────────────────┤         ├───────────────────┤
│ id (UUID)           │         │ id (UUID)         │
│ user_id (FK)        │         │ ...               │
│ plan_id (FK)        │         │ active_plan_id    │
│ source (admin/buy)  │ ◄───┐   │ plan_starts_at    │
│ started_at          │     │   │ trial_ends_at     │
│ expires_at          │     │   │ stripe_*_id       │
│ status (A/E/C)      │     └───┤ ...               │
│ stripe_sub_id       │         │                   │
│ price_paid          │         └───────────────────┘
│ notes               │
│ timestamps          │
│ soft deletes        │         ┌────────────────┐
└─────────────────────┘         │  plans (exist) │
                                ├────────────────┤
                                │ id             │
                                │ name           │
                                │ price          │
                                │ billing_cycle  │
                                │ features       │
                                │ ...            │
                                └────────────────┘
```

## Data Flow Diagrams

### Flow 1: Admin Assigns Plan

```
Admin User
    │
    └─→ Click "Assign Plan" button
        │
        └─→ POST /admin/users/{id}/assign-plan
            │
            └─→ AdminUserController.assignPlan()
                │
                └─→ SubscriptionService.assignPlanToUser()
                    │
                    ├─1. Transaction Start
                    │
                    ├─2. Mark existing subscriptions as expired
                    │   └─→ UPDATE subscriptions SET status='expired'
                    │
                    ├─3. Create new subscription
                    │   └─→ INSERT INTO subscriptions VALUES (
                    │       user_id, plan_id, source='admin', 
                    │       started_at=NOW(), expires_at=NULL, 
                    │       status='active', ...)
                    │
                    ├─4. Update user's active_plan_id
                    │   └─→ UPDATE users SET active_plan_id={plan_id}
                    │
                    ├─5. Transaction Commit
                    │
                    └─→ Return success message

Result:
✓ User has new subscription
✓ Previous subscriptions marked expired
✓ active_plan_id updated
✓ All changes atomic (all-or-nothing)
```

### Flow 2: User Purchases/Changes Plan

```
User
    │
    └─→ Navigate to /settings/subscriptions
        │
        ├─→ View current plan
        ├─→ View available plans
        │
        └─→ Click "Choose Plan" on different plan
            │
            └─→ POST /settings/subscriptions/change-plan
                │
                └─→ SubscriptionController.changePlan()
                    │
                    └─→ SubscriptionService.changePlan($user, $plan, 'purchase')
                        │
                        ├─1. Transaction Start
                        │
                        ├─2. Mark existing subscriptions as expired
                        │   └─→ UPDATE subscriptions SET status='expired'
                        │
                        ├─3. Calculate expiration date
                        │   └─→ started_at + billing_cycle duration
                        │
                        ├─4. Create new subscription
                        │   └─→ INSERT INTO subscriptions VALUES (
                        │       user_id, plan_id, source='purchase', 
                        │       started_at=NOW(), 
                        │       expires_at={calculated_date},
                        │       status='active', price_paid={plan.price}, ...)
                        │
                        ├─5. Update user's active_plan_id
                        │   └─→ UPDATE users SET active_plan_id={plan_id}
                        │
                        ├─6. Transaction Commit
                        │
                        └─→ Return success message

Result:
✓ User has new subscription
✓ Expiration date calculated and set
✓ Price paid recorded
✓ Active plan updated
✓ Previous subscriptions in history
```

### Flow 3: User Renews Subscription

```
User
    │
    └─→ View current subscription
        │
        └─→ Click "Renew Subscription" button
            │
            └─→ POST /settings/subscriptions/rebuy
                │
                └─→ SubscriptionController.rebuy()
                    │
                    └─→ Get currentSubscription
                        │
                        └─→ SubscriptionService.createPurchasedSubscription(
                            $user, $currentPlan)
                            │
                            ├─1. Transaction Start
                            │
                            ├─2. Mark existing subscriptions as expired
                            │
                            ├─3. Create SAME plan subscription again
                            │   └─→ INSERT INTO subscriptions VALUES (
                            │       user_id, current_plan_id, 
                            │       source='purchase', started_at=NOW(), 
                            │       expires_at={now+billing_cycle}, ...)
                            │
                            ├─4. Update user's active_plan_id
                            │
                            ├─5. Transaction Commit
                            │
                            └─→ Return success

Result:
✓ Subscription renewed for another cycle
✓ Start date reset to today
✓ Expiration date extended
✓ Old subscription in history
```

## Database Schema Detail

### subscriptions table

```sql
CREATE TABLE subscriptions (
    id CHAR(36) PRIMARY KEY,              -- UUID
    user_id CHAR(36) NOT NULL,            -- Foreign key to users
    plan_id CHAR(36) NOT NULL,            -- Foreign key to plans
    source VARCHAR(20) DEFAULT 'admin',   -- 'admin' or 'purchase'
    
    -- Timeline
    started_at TIMESTAMP NOT NULL,        -- When subscription started
    expires_at TIMESTAMP NULL,            -- When subscription expires (null = no expiration)
    
    -- Status
    status VARCHAR(20) DEFAULT 'active',  -- 'active', 'expired', 'cancelled'
    
    -- Payment tracking
    stripe_subscription_id VARCHAR(255) NULL,
    price_paid DECIMAL(10, 2) NULL,       -- Amount paid for this subscription
    
    -- Admin notes
    notes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,            -- Soft delete
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_started_at (started_at)
);
```

### users table (additions)

```sql
ALTER TABLE users ADD (
    active_plan_id CHAR(36) NULL,         -- Current active plan
    plan_starts_at TIMESTAMP NULL,        -- When current plan started
    trial_ends_at TIMESTAMP NULL,         -- When trial ends (legacy)
    stripe_customer_id VARCHAR(255) NULL,
    stripe_subscription_id VARCHAR(255) NULL
);

-- Relationships
ALTER TABLE users ADD FOREIGN KEY (active_plan_id) 
    REFERENCES plans(id) ON DELETE SET NULL;
```

## Service Method Call Hierarchy

```
Public Methods (User-Facing)
│
├─ assignPlanToUser(user, plan, expiresAt, notes)
│  └─ Private: expireUserSubscriptions(user)
│  └─ Creates: Subscription record with source='admin'
│  └─ Updates: user.active_plan_id
│
├─ createPurchasedSubscription(user, plan, stripeId, price)
│  └─ Private: expireUserSubscriptions(user)
│  └─ Private: calculateExpirationDate(plan)
│  └─ Creates: Subscription with source='purchase'
│  └─ Updates: user.active_plan_id
│
├─ changePlan(user, newPlan, source)
│  └─ Calls: expireUserSubscriptions(user)
│  └─ Calls: assignPlanToUser() or createPurchasedSubscription()
│
├─ extendSubscription(user, days)
│  └─ Gets: currentSubscription(user)
│  └─ Updates: subscription.expires_at
│  └─ Updates: user.trial_ends_at (if on trial)
│
├─ cancelSubscription(user, reason)
│  └─ Gets: currentSubscription(user)
│  └─ Updates: status='cancelled'
│  └─ Updates: user.active_plan_id = null
│
├─ getSubscriptionHistory(user)
│  └─ Returns: All subscriptions ordered by started_at DESC
│
├─ getCurrentPlanDetails(user)
│  └─ Returns: Array with plan details or null
│
└─ hasActiveSubscription(user)
   └─ Returns: Boolean
```

## State Transitions

### Subscription Status Flow

```
┌─────────────────────────────────────────────────────────┐
│                    CREATED (new subscription)            │
└────────────────────────┬────────────────────────────────┘
                         │
                    Set to 'active'
                         │
                    ┌────▼────┐
                    │  ACTIVE  │
                    └────┬────┘
                         │
          ┌──────────────┼──────────────┐
          │              │              │
     Expires      User calls        Admin calls
     naturally    cancel()          extension
          │              │              │
          ▼              ▼              ▼
    ┌──────────┐  ┌────────────┐  New ACTIVE
    │ EXPIRED  │  │ CANCELLED  │  subscription
    └──────────┘  └────────────┘  (old expired)
          │              │
          └──────┬───────┘
                 │
         Kept in history
         (soft deleted)
```

## Access Control

```
┌──────────────────────────────────────────────────────┐
│            SUBSCRIPTION ACCESS CONTROL              │
├──────────────────────────────────────────────────────┤
│                                                      │
│ User can:                                            │
│ ✓ View their current subscription                    │
│ ✓ View their subscription history                    │
│ ✓ View available plans                               │
│ ✓ Change to different plan                           │
│ ✓ Renew current subscription                         │
│ ✓ Cancel subscription                                │
│ ✗ View other users' subscriptions                    │
│ ✗ Modify other users' plans                          │
│                                                      │
│ Admin can:                                           │
│ ✓ Assign any plan to any user                        │
│ ✓ Extend any user's subscription                     │
│ ✓ View any user's subscription history               │
│ ✓ See subscription details on admin dashboard        │
│                                                      │
└──────────────────────────────────────────────────────┘
```

## Error Handling

```
SubscriptionService Error Handling
│
├─ assignPlanToUser
│  ├─ Plan not found → Exception
│  ├─ User not found → Exception
│  ├─ DB transaction fails → Rollback
│  └─ Success → Return Subscription
│
├─ createPurchasedSubscription
│  ├─ Invalid plan → Exception
│  ├─ Invalid user → Exception
│  ├─ Calculation error → Exception
│  └─ Success → Return Subscription
│
├─ extendSubscription
│  ├─ No active subscription → Exception
│  ├─ Invalid days → Validation error
│  └─ Success → Return Subscription
│
├─ cancelSubscription
│  ├─ No active subscription → Exception
│  └─ Success → Return Subscription
│
└─ All operations atomic
   ├─ Success → All changes committed
   └─ Failure → All changes rolled back
```

## Performance Considerations

### Database Indexes

```sql
-- Fast lookups by user
CREATE INDEX idx_subscriptions_user_id ON subscriptions(user_id);

-- Fast lookups by status
CREATE INDEX idx_subscriptions_status ON subscriptions(status);

-- Fast lookups by date
CREATE INDEX idx_subscriptions_started_at ON subscriptions(started_at);

-- Combined query optimization
CREATE INDEX idx_subscriptions_user_status 
    ON subscriptions(user_id, status);
```

### Query Optimization

```php
// Efficient: Single query with eager loading
$subscription = Subscription::with('plan', 'user')
    ->where('user_id', $userId)
    ->active()
    ->first();

// Inefficient: N+1 queries
$subscriptions = Subscription::all();
foreach ($subscriptions as $sub) {
    $plan = $sub->plan;  // Extra query!
}
```

## Scalability

The system is designed to scale:

1. **Database**: Indexed queries ensure O(1) lookups
2. **Caching**: Can add Redis caching for active subscriptions
3. **Transactions**: Atomic operations prevent race conditions
4. **Soft Deletes**: Archive old subscriptions without deleting
5. **Status Tracking**: Easy filtering and reporting

### Example Caching Strategy

```php
// Cache active subscription for 1 hour
$subscription = Cache::remember(
    "subscription.user.{$userId}.active",
    3600,
    function () use ($userId) {
        return Subscription::getCurrentSubscription(
            User::find($userId)
        );
    }
);

// Invalidate on change
Cache::forget("subscription.user.{$userId}.active");
```

## Monitoring & Analytics

The system enables easy analytics:

```php
// Active subscriptions by plan
Subscription::active()
    ->groupBy('plan_id')
    ->selectRaw('plan_id, COUNT(*) as count')
    ->get();

// Subscription retention
Subscription::where('status', 'expired')
    ->whereBetween('expires_at', [$start, $end])
    ->count();

// Admin vs user-purchased
Subscription::where('source', 'admin')->count();
Subscription::where('source', 'purchase')->count();

// Revenue tracking
Subscription::where('source', 'purchase')
    ->sum('price_paid');
```

---

**System Status**: ✅ Complete and Ready
**Last Updated**: June 11, 2026
**Version**: 1.0
