# Quick Start - Subscription System

## Access Points

### For Users
- **URL**: `http://127.0.0.1:8001/settings/subscriptions`
- **Via Settings**: Click Settings → Click "Plans & Subscriptions" in sidebar

### For Admins
- **User Detail Page**: `http://127.0.0.1:8001/admin/users/{user-id}`
  - Shows current plan
  - Shows subscription history
  - Can assign new plan
  - Can extend subscription

## Quick Actions

### User Actions
```
/settings/subscriptions

1. View Current Plan
   - Plan name, price, billing cycle
   - Start date and expiration date
   - Days remaining
   - How it was obtained (admin/purchased)

2. Change Plan
   - Browse available plans
   - Click "Choose Plan"
   - Confirm modal
   - Subscription updated immediately

3. Renew Subscription
   - Click "Renew Subscription" button
   - Confirm modal
   - Subscription extended for another billing cycle

4. Cancel Subscription
   - Click "Cancel Subscription" button
   - Confirm deletion warning
   - Subscription cancelled
   - Access to plan features removed

5. View History
   - See all past subscriptions
   - Track plan changes
   - See expiration dates
   - Filter by status
```

### Admin Actions
```
/admin/users/{user-id}

1. Assign Plan
   - Click "💳 Assign Plan" button (green)
   - Select plan from dropdown
   - Click "Assign Plan" in modal
   - Subscription created for user
   - Previous subscriptions auto-expired

2. Extend Subscription
   - Click "⏳ Extend Trial Days" button (blue)
   - Enter number of days (1-365)
   - Click "Extend Trial"
   - Subscription extended by that many days

3. View Subscription Details
   - Current plan shown in "Trial & Subscription" card
   - Plan name, price, billing cycle displayed
   - Subscription start date shown
   - View subscription history table below
```

## Database Tables Reference

### subscriptions table
```sql
SELECT * FROM subscriptions WHERE user_id = '{user_id}';

-- View active subscription
SELECT * FROM subscriptions 
WHERE user_id = '{user_id}' 
AND status = 'active'
ORDER BY started_at DESC
LIMIT 1;

-- View all history
SELECT * FROM subscriptions 
WHERE user_id = '{user_id}'
ORDER BY started_at DESC;

-- Find admin-assigned vs purchased
SELECT source, COUNT(*) FROM subscriptions 
WHERE status = 'active'
GROUP BY source;
```

## API Endpoints

### User Endpoints
```
GET  /settings/subscriptions
     → Show subscriptions page with current plan, history, and available plans

POST /settings/subscriptions/change-plan
     → Change to different plan
     Parameters: plan_id

POST /settings/subscriptions/rebuy
     → Renew current subscription
     Parameters: none

POST /settings/subscriptions/cancel
     → Cancel subscription
     Parameters: none
```

### Admin Endpoints
```
POST /admin/users/{user}/assign-plan
     → Assign plan to user
     Parameters: plan_id

POST /admin/users/{user}/extend-trial
     → Extend subscription
     Parameters: days (1-365)
```

## Service Layer Usage

```php
// Get service
$service = app(\App\Services\SubscriptionService::class);

// Admin assign plan
$subscription = $service->assignPlanToUser(
    $user,
    $plan,
    $expiresAt = null,  // optional expiration date
    $notes = null       // optional notes
);

// User purchase plan
$subscription = $service->createPurchasedSubscription(
    $user,
    $plan,
    $stripeSubscriptionId = null,
    $pricePaid = null
);

// Change plan
$subscription = $service->changePlan($user, $newPlan, $source = 'admin');

// Extend subscription
$subscription = $service->extendSubscription($user, $days);

// Cancel subscription
$subscription = $service->cancelSubscription($user, $reason = null);

// Get current details
$details = $service->getCurrentPlanDetails($user);

// Get history
$history = $service->getSubscriptionHistory($user);

// Check if active
$hasActive = $service->hasActiveSubscription($user);
```

## Subscription Object Structure

```php
$subscription = Subscription::find($id);

// Properties
$subscription->user_id              // UUID
$subscription->plan_id              // UUID
$subscription->source               // 'admin' or 'purchase'
$subscription->started_at           // DateTime when subscription started
$subscription->expires_at           // DateTime when subscription expires (null = no expiration)
$subscription->status               // 'active', 'expired', 'cancelled'
$subscription->stripe_subscription_id // Stripe ID if purchased
$subscription->price_paid           // Amount paid for this subscription
$subscription->notes                // Admin notes

// Methods
$subscription->user()               // Get the user
$subscription->plan()               // Get the plan
$subscription->isActive()           // Check if currently active
$subscription->markAsExpired()      // Mark as expired

// Query
User::find($id)->currentSubscription()      // Get active subscription
User::find($id)->subscriptions()             // Get all subscriptions
Subscription::active()->get()                // Get all active subscriptions
Subscription::expired()->get()               // Get all expired subscriptions
```

## Key Files

| File | Purpose |
|------|---------|
| `app/Models/Subscription.php` | Subscription model with relationships |
| `app/Services/SubscriptionService.php` | All business logic for subscriptions |
| `app/Http/Controllers/SubscriptionController.php` | User-facing subscription endpoints |
| `routes/web.php` | Subscription routes and admin routes |
| `resources/js/Pages/Settings/Subscriptions.vue` | User subscriptions page UI |
| `resources/js/Components/Settings/SettingsSidebar.vue` | Updated sidebar with subscriptions link |
| `database/migrations/2026_06_11_044129_create_subscriptions_table.php` | Database migration |

## Status Reference

| Status | Meaning | Can Use Plan? |
|--------|---------|--------------|
| `active` | Subscription is currently valid | ✅ Yes |
| `expired` | Subscription has ended or was cancelled | ❌ No |
| `cancelled` | User cancelled the subscription | ❌ No |

## Source Reference

| Source | Meaning | Who Controls? |
|--------|---------|---------------|
| `admin` | Plan assigned by administrator | Admin can extend |
| `purchase` | Plan purchased/renewed by user | User can renew/change |

## Example Scenarios

### Scenario 1: Admin Assigns Trial Plan
```
Admin navigates to: /admin/users/019e6348-70cc-72bc-8d31-f74f80a582fe
Clicks: "Assign Plan" button
Selects: "Trial" plan
System:
  ✓ Creates subscription (source='admin', status='active')
  ✓ Sets expires_at = null (no expiration for admin)
  ✓ Updates user.active_plan_id
  ✓ Marks previous subscriptions as expired
User can see:
  ✓ Plan in /settings/subscriptions
  ✓ Start date = today
  ✓ No expiration date
  ✓ Badge showing "Admin Assigned"
```

### Scenario 2: User Purchases Monthly Plan
```
User navigates to: /settings/subscriptions
Sees: "Professional" plan at $49/month
Clicks: "Choose Plan" button
Confirms: Modal confirmation
System:
  ✓ Creates subscription (source='purchase', status='active')
  ✓ Sets expires_at = today + 1 month
  ✓ Sets price_paid = 49.00
  ✓ Updates user.active_plan_id
  ✓ Marks previous subscriptions as expired
User can see:
  ✓ Updated plan in /settings/subscriptions
  ✓ Start date = today
  ✓ Expiration date = next month
  ✓ Days remaining countdown
  ✓ Badge showing "Purchased"
```

### Scenario 3: User Cancels Subscription
```
User navigates to: /settings/subscriptions
Sees: "Cancel Subscription" button
Clicks: Button
Confirms: Warning modal "Are you sure?"
System:
  ✓ Marks subscription as status='cancelled'
  ✓ Clears user.active_plan_id
  ✓ User loses plan access immediately
User can see:
  ✓ No current active plan
  ✓ Message "No Active Subscription"
  ✓ Can view cancelled subscription in history
  ✓ Can choose new plan
```

## Testing

### Test Admin Assignment
```
1. Go to /admin/users/{any-user-id}
2. Click "Assign Plan" (green button)
3. Select any plan
4. Verify user's subscription page shows it
```

### Test User Plan Change
```
1. Go to /settings/subscriptions (as any user)
2. Choose a plan different from current
3. Confirm change
4. Verify plan updated and history shows both
```

### Test Subscription History
```
1. As admin, assign user a plan
2. As user, change to another plan
3. As user, cancel and renew same plan
4. Go to /settings/subscriptions
5. View history table showing all 4 subscriptions
```

## Troubleshooting

### Issue: User doesn't see subscription page
**Solution**: Check sidebar - "Plans & Subscriptions" should be under "Billing" section

### Issue: Admin assign doesn't work
**Solution**: 
1. Verify plan exists and is_active = true
2. Check plan_id format is UUID
3. Verify user exists

### Issue: Subscription history empty
**Solution**:
1. User must have at least one subscription record
2. Check subscriptions table for user_id
3. Confirm migration ran successfully

### Issue: Expiration date not showing
**Solution**:
1. For admin-assigned plans: expires_at is null (no expiration)
2. For user-purchased: expires_at should be set
3. Check subscription.expires_at value in database

## Support

For detailed documentation, see:
- `SUBSCRIPTION_SYSTEM_GUIDE.md` - Complete system documentation
- `IMPLEMENTATION_SUMMARY.md` - What was built and why
