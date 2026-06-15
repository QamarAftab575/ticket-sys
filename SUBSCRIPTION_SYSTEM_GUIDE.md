# Subscription System Implementation Guide

## Overview
A complete subscription management system has been implemented where both admin-assigned and user-purchased plans are stored in a unified `subscriptions` table with full history tracking.

## Database Schema

### Subscriptions Table
```sql
- id (UUID, primary)
- user_id (UUID, foreign key to users)
- plan_id (UUID, foreign key to plans)
- source (string): 'admin' or 'purchase'
- started_at (timestamp)
- expires_at (timestamp, nullable)
- status (string): 'active', 'expired', 'cancelled'
- stripe_subscription_id (string, nullable)
- price_paid (decimal, nullable)
- notes (text, nullable)
- created_at, updated_at, deleted_at
```

## Key Features

### 1. Admin Plan Assignment
- **Location**: `/admin/users/{id}` → "Assign Plan" button
- **Flow**: 
  - Admin selects a plan from modal
  - SubscriptionService creates new Subscription record with source='admin'
  - Any previous active subscriptions are marked as expired
  - User's `active_plan_id` is updated
  
### 2. User Subscription Management
- **Location**: `/settings/subscriptions`
- **Available Actions**:
  - View current active plan with expiration date
  - View complete subscription history
  - Change to a different plan
  - Renew current subscription
  - Cancel subscription

### 3. Unified Data Storage
All subscriptions (admin-assigned or user-purchased) are stored in the same table:
```
✓ Admin assigns plan → subscriptions.source = 'admin'
✓ User purchases plan → subscriptions.source = 'purchase'
✓ Only ONE active subscription per user at a time
✓ Expired/cancelled subscriptions kept in history with soft delete
```

## Models

### Subscription Model
```php
class Subscription extends Model {
    - user() → belongsTo(User)
    - plan() → belongsTo(Plan)
    - scopeActive() → returns active subscriptions
    - scopeExpired() → returns expired subscriptions
    - isActive() → checks if subscription is currently active
    - markAsExpired() → marks subscription as expired
}
```

### User Model Updates
```php
// Added relationships
- subscriptions() → hasMany(Subscription)
- currentSubscription() → gets active subscription
```

### Plan Model
- Already existed with `hasMany(User)` relationship
- New: `hasMany(Subscription)` relationship

## Services

### SubscriptionService
Handles all subscription business logic:

```php
// Admin assignment
assignPlanToUser(User $user, Plan $plan, ?Carbon $expiresAt, ?string $notes)

// User purchase
createPurchasedSubscription(User $user, Plan $plan, ?string $stripeId, ?float $price)

// Plan changes
changePlan(User $user, Plan $newPlan, ?string $source)

// Subscription management
extendSubscription(User $user, int $days)
cancelSubscription(User $user, ?string $reason)
getSubscriptionHistory(User $user)

// Helpers
hasActiveSubscription(User $user): bool
getCurrentPlanDetails(User $user): array
```

## Controllers

### SubscriptionController (User-Facing)
- `show()` → Display subscriptions page with history and available plans
- `changePlan()` → Change to different plan
- `rebuy()` → Renew current subscription
- `cancel()` → Cancel subscription

### AdminUserController (Admin)
- Updated `assignPlan()` → Uses SubscriptionService
- Updated `extendTrial()` → Uses SubscriptionService

## Routes

### User Routes
```php
GET  /settings/subscriptions               → show subscriptions page
POST /settings/subscriptions/change-plan   → change plan
POST /settings/subscriptions/cancel        → cancel subscription
POST /settings/subscriptions/rebuy         → renew subscription
```

### Admin Routes (unchanged)
```php
POST /admin/users/{user}/assign-plan       → assign plan
POST /admin/users/{user}/extend-trial      → extend subscription
```

## Frontend Pages

### 1. User Subscriptions Page
**URL**: `/settings/subscriptions`

**Features**:
- Current subscription details (plan name, price, expiration)
- Days remaining indicator
- Subscription source badge (Admin/Purchased)
- Available plans grid with "Choose Plan" buttons
- Subscription history table showing all past plans
- Action buttons:
  - "Change Plan" → Switch to different plan
  - "Renew Subscription" → Extend current plan
  - "Cancel Subscription" → Cancel with confirmation

### 2. Admin User Page
**URL**: `/admin/users/{id}`

**Enhanced**:
- Shows current plan with start date (no expiration unless from trial)
- History shows past subscriptions
- "Assign Plan" button to give user a plan
- "Extend Trial Days" button for trial users only

## Data Flow Examples

### Scenario 1: Admin Assigns Plan
```
Admin → Click "Assign Plan" → Select Plan → Confirm
↓
1. Mark existing subscriptions as expired
2. Create new Subscription (source='admin')
3. Update user.active_plan_id
4. Return success message
```

### Scenario 2: User Changes Plan
```
User → /settings/subscriptions → Click "Choose Plan" → Confirm
↓
1. Mark current subscription as expired
2. Create new Subscription (source='purchase')
3. Calculate expiration date (started_at + billing_cycle)
4. Update user.active_plan_id
5. Return success message
```

### Scenario 3: User Renews Subscription
```
User → /settings/subscriptions → Click "Renew Subscription" → Confirm
↓
1. Mark current subscription as expired
2. Create new identical Subscription (source='purchase')
3. Calculate new expiration date
4. Update user.active_plan_id
5. Return success message
```

## Key Business Rules

1. **One Active Subscription Per User**
   - Only one subscription can have status='active' at a time
   - New subscriptions automatically expire previous ones

2. **Expiration Handling**
   - Subscriptions expire based on billing_cycle (monthly, quarterly, semi-annual, yearly)
   - Expired subscriptions kept for history (soft deleted not visible)
   - Manual extension possible for admin users

3. **Status Tracking**
   - `active` → Currently valid subscription
   - `expired` → Past or ended subscription
   - `cancelled` → User-cancelled subscription

4. **Source Tracking**
   - `admin` → Assigned by administrator
   - `purchase` → Purchased/renewed by user
   - Helps distinguish between different subscription sources

## Integration with Existing System

### Trial System
- Old trial system (`user.trial_ends_at`) still functional
- New subscription system runs parallel
- When user purchases plan, trial is automatically cleared

### Plan Features
- Plans already have features, pricing, and billing_cycle
- Subscriptions now track which specific plan instance was used
- Price history retained via `price_paid` field

### Future Enhancements
- Stripe integration for payment processing
- Automatic expiration handling via scheduled jobs
- Invoice generation
- Subscription upgrade/downgrade logic
- Proration calculations

## Testing the System

### Admin Assignment
```php
// In Tinker or test
$user = User::first();
$plan = Plan::first();
$service = app(\App\Services\SubscriptionService::class);
$subscription = $service->assignPlanToUser($user, $plan);
```

### User Purchase
```php
$subscription = $service->createPurchasedSubscription($user, $plan);
```

### Check Active Subscription
```php
$current = $user->currentSubscription();
$details = $service->getCurrentPlanDetails($user);
```

### Subscription History
```php
$history = $service->getSubscriptionHistory($user);
```

## Files Created/Modified

### New Files
- `database/migrations/2026_06_11_044129_create_subscriptions_table.php`
- `app/Models/Subscription.php`
- `app/Services/SubscriptionService.php`
- `app/Http/Controllers/SubscriptionController.php`
- `resources/js/Pages/Settings/Subscriptions.vue`

### Modified Files
- `app/Models/User.php` - Added subscription relationships
- `app/Http/Controllers/Admin/AdminUserController.php` - Updated to use SubscriptionService
- `routes/web.php` - Added subscription routes and import
- `resources/js/Components/Settings/SettingsSidebar.vue` - Added Subscriptions link

## API Reference

### SubscriptionService Methods

#### assignPlanToUser()
```php
assignPlanToUser(User $user, Plan $plan, ?Carbon $expiresAt = null, ?string $notes = null)
// Returns: Subscription
```

#### createPurchasedSubscription()
```php
createPurchasedSubscription(User $user, Plan $plan, ?string $stripeSubscriptionId = null, ?float $pricePaid = null)
// Returns: Subscription
```

#### changePlan()
```php
changePlan(User $user, Plan $newPlan, ?string $source = 'admin')
// Returns: Subscription
```

#### extendSubscription()
```php
extendSubscription(User $user, int $days)
// Returns: Subscription
```

#### cancelSubscription()
```php
cancelSubscription(User $user, ?string $reason = null)
// Returns: Subscription
```

#### getCurrentPlanDetails()
```php
getCurrentPlanDetails(User $user)
// Returns: array with plan details or null
```

## Notes

- The system uses UUIDs for all IDs
- Soft deletes are used for audit trail
- All plan changes are wrapped in transactions for data integrity
- The subscription history is queryable for analytics
- Source tracking enables reporting on how plans were obtained
