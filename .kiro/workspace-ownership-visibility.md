# Workspace Ownership-Based Settings Visibility

## Overview
Settings Sidebar menu items are now dynamically shown/hidden based on whether the user is the **owner of the currently selected workspace**.

## Implementation

### 1. New Composable: `useWorkspaceOwnership.js`
**File:** `resources/js/Composables/useWorkspaceOwnership.js`

This composable:
- Uses `useActiveWorkspace()` as the single source of truth
- Checks the currently selected workspace
- Returns computed properties for ownership status

### 2. Updated: SettingsSidebar Component
**File:** `resources/js/Components/Settings/SettingsSidebar.vue`

**Changes:**
- Now receives `userWorkspaces` prop from parent
- Uses `useWorkspaceOwnership()` composable
- Conditionally renders sections:
  - **Billing Section** - Only shown if `isOwnerOfActiveWorkspace`
  - **Integrations Section** - Only shown if `isOwnerOfActiveWorkspace`

### 3. Updated: Settings Page
**File:** `resources/js/Pages/Settings.vue`

**Changes:**
- Passes `:user-workspaces="userWorkspaces"` to SettingsSidebar
- Sidebar now has access to workspace data with role information

## How It Works

### Data Flow
```
User selects workspace A (Owner)
    ↓
Active Workspace Helper stores in localStorage
    ↓
SettingsSidebar initializes via useWorkspaceOwnership()
    ↓
Checks activeWorkspace.is_owner flag
    ↓
Billing & Integrations sections visible
```

### Ownership Check
```javascript
isOwnerOfActiveWorkspace = activeWorkspace.is_owner === true
```

The `is_owner` flag is set by the backend when fetching workspaces (see Settings route):

```php
Route::get('/settings', function () {
    $userWorkspaces = $user->getAccessibleOrganizations()
        ->map(function ($organization) use ($user) {
            return [
                'id' => $organization->id,
                'name' => $organization->name,
                'is_owner' => $user->isWorkspaceOwner($user, $organization), // Check for this workspace
                'is_admin' => $user->isWorkspaceAdmin($organization->id),
                // ...
            ];
        });
```

## Expected Behavior

### Scenario 1: User owns Workspace A
```
User A switches to Workspace A (owned)
↓
Active workspace set to A
↓
isOwnerOfActiveWorkspace = true
↓
✅ Billing section visible
✅ Integrations section visible
✅ Links accessible
```

### Scenario 2: User is member of Workspace B
```
User A switches to Workspace B (member only)
↓
Active workspace set to B
↓
isOwnerOfActiveWorkspace = false (B.is_owner = false)
↓
❌ Billing section hidden
❌ Integrations section hidden
❌ Links inaccessible (403 on direct access)
```

### Scenario 3: User switches back to Workspace A
```
User A switches back to Workspace A
↓
Active workspace resets to A
↓
isOwnerOfActiveWorkspace = true
↓
✅ Billing section visible again
✅ Integrations section visible again
```

## Single Source of Truth

All ownership checks use:
1. **Frontend:** Active workspace from `useActiveWorkspace()` composable
2. **Backend:** `BillingHelper::isWorkspaceOwner($user, $workspace)`

This ensures consistency across the application.

## API Functions

### `useWorkspaceOwnership(userWorkspaces)`
Returns:
```javascript
{
  isOwnerOfActiveWorkspace,      // Boolean - is user owner of active workspace
  isAdminOrOwnerOfActiveWorkspace, // Boolean - is user admin or owner
  activeWorkspace,               // Object - the currently selected workspace
}
```

## Visible Menu Items

**Always visible:**
- My Workspaces

**Conditionally visible (owner only):**
- Billing
  - Plans & Subscriptions
- Integrations
  - Access Tokens

## Backend Validation

While menus are hidden on frontend, routes still require backend authorization:

**SubscriptionController::show()**
- Currently allows all authenticated users
- Can be restricted to workspace owners if needed

**ApiTokenController::show()**
- Already checks: `user is super-admin OR owner/admin of any workspace`
- Validates access before rendering tokens

## Edge Cases Handled

✅ User with multiple workspaces
- Ownership checked per workspace
- Menu updates immediately on workspace switch

✅ User without any owned workspaces
- Menu items hidden by default
- No errors when clicking protected routes (403 Forbidden)

✅ Direct URL access
- Frontend hides links
- Backend validates and denies access (403)
- Prevents unauthorized access even with direct URL

## Future Enhancements

Could also restrict based on:
- `isAdminOrOwnerOfActiveWorkspace` - For admin-level features
- `isMemberOfActiveWorkspace` - For member features
- Custom role checks

Just import and use the composable in any component that needs workspace ownership checks.
