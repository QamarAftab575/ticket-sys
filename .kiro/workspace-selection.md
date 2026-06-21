# Active Workspace Selection System

## Overview
Centralized workspace selection management using browser localStorage. **No database persistence.**

## Single Source of Truth
**File:** `resources/js/Composables/useActiveWorkspace.js`

All workspace selection logic is centralized here. No duplicated logic across components.

## How It Works

### 1. **Storage**
- **Browser Storage:** localStorage with key `asira_active_workspace`
- **Persistence:** Survives browser restarts, days/weeks/months later
- **No Database:** Workspace selection is NOT saved to database

### 2. **Initialization**
When the app loads:
1. Checks localStorage for saved workspace
2. If found and valid, uses it
3. If not found, automatically selects first available workspace
4. Saves selection to localStorage

### 3. **Switching Workspaces**
When user switches:
1. `setActiveWorkspace(workspace)` saves to localStorage
2. UI updates immediately
3. Router navigates to workspace

## API Functions

### `useActiveWorkspace(workspaces)`
Vue composable for components - returns reactive properties:
```javascript
const { activeWorkspace, switchWorkspace } = useActiveWorkspace(userWorkspaces)

// Use in template:
// {{ activeWorkspace.name }}
// @click="switchWorkspace(workspace)"
```

### `initializeActiveWorkspace(availableWorkspaces)`
Initialize on component mount:
```javascript
onMounted(() => {
  initializeActiveWorkspace(userWorkspaces.value)
})
```

### `setActiveWorkspace(workspace)`
Manually set active workspace:
```javascript
setActiveWorkspace(workspace)  // Saves to localStorage
```

### `getActiveWorkspace(availableWorkspaces)`
Get active workspace object:
```javascript
const active = getActiveWorkspace(workspaces)
```

### `getActiveWorkspaceId()`
Get just the ID:
```javascript
const id = getActiveWorkspaceId()
```

### `clearActiveWorkspace()`
Clear on logout:
```javascript
clearActiveWorkspace()  // Removes from localStorage
```

## Components Using This

1. **WorkspaceSwitcher.vue** - Main workspace selector dropdown
2. **Workspace/Sidebar.vue** - Sidebar workspace selector
3. Any component using `useActiveWorkspace()` composable

## Backend

**Controller:** `WorkspaceDashboardController::switchWorkspace()`
- Only validates user access
- No session/database storage
- Redirects to workspace dashboard

## Usage Example

```vue
<script setup>
import { onMounted } from 'vue'
import { useActiveWorkspace } from '@/Composables/useActiveWorkspace'

const workspaces = ref([])
const { activeWorkspace, switchWorkspace, initializeActiveWorkspace } = useActiveWorkspace(workspaces)

onMounted(() => {
  // Initialize with available workspaces
  initializeActiveWorkspace(workspaces.value)
})

const handleSwitch = (workspace) => {
  switchWorkspace(workspace)
  // UI updates automatically via computed property
}
</script>

<template>
  <div>
    <p>Current: {{ activeWorkspace?.name }}</p>
    <button @click="handleSwitch(workspace)">Switch</button>
  </div>
</template>
```

## Storage Key
- **Key:** `asira_active_workspace`
- **Value:** Workspace ID (UUID)
- **Location:** Browser localStorage

## Cleanup
When user logs out, call:
```javascript
clearActiveWorkspace()
```

This removes from localStorage and resets state.
