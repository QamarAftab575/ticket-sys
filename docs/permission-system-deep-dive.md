# Permission System Deep Dive

## Overview

This system implements a **two-level role hierarchy** using Spatie Permission with workspace-scoped project roles. It combines custom pivot tables for membership tracking with Spatie's permission system for authorization checks.

---

## Architecture Layers

### Layer 1: Custom Membership Tables (Source of Truth)
Your own tables track who belongs where and with what role:

- `organization_memberships` — workspace-level membership
- `project_members` — project-level membership

These tables are the **source of truth** for membership and roles. They store:
- Who is in which workspace/project
- What role they have
- How they joined (`access_type`: team_member, direct_invite, task_assignee)
- When they joined and who added them

### Layer 2: Spatie Permission Tables (Authorization Engine)
Spatie's tables handle the permission checks:

- `roles` — stores role definitions (project_admin, editor, commenter, viewer, workspace_owner, workspace_member)
- `permissions` — stores permission definitions (project.view, project.edit, etc.)
- `role_has_permissions` — maps which permissions each role has
- `model_has_roles` — assigns roles to users (with `workspace_id` scoping)

---

## Database Schema

### Your Custom Tables

#### `organizations` (workspaces)
```
id (uuid)
name
description
created_by (uuid) → users.id
avatar_color
is_active
timestamps, soft_deletes
```

#### `organization_memberships` (workspace members)
```
id (uuid)
organization_id (uuid) → organizations.id
user_id (uuid) → users.id
role (enum: 'owner', 'member')
joined_at
is_active
timestamps, soft_deletes
UNIQUE(organization_id, user_id)
```

#### `projects`
```
id (uuid)
organization_id (uuid) → organizations.id
name
description
manager_id (uuid) → users.id
owner_id (uuid) → users.id
status (enum: on_track, at_risk, off_track, on_hold, complete, archived)
visibility (enum: public_to_team, private_to_members)
privacy (enum: public_to_team, private, specific_members)
color, icon
start_date, target_date
created_by (uuid) → users.id
archived_at
timestamps, soft_deletes
```

#### `project_members` (project collaborators)
```
id (uuid)
project_id (uuid) → projects.id
user_id (uuid) → users.id
role (enum: 'project_admin', 'editor', 'commenter', 'viewer')
access_type (enum: 'team_member', 'direct_invite', 'task_assignee')
invited_by (uuid, nullable) → users.id
assigned_at
assigned_by (uuid) → users.id
timestamps
UNIQUE(project_id, user_id)
```

#### `organization_invitations` (workspace invites)
```
id (uuid)
organization_id (uuid) → organizations.id
email
role (default: 'member')
invited_by (uuid) → users.id
token (unique)
expires_at
accepted_at (nullable)
timestamps
```

#### `project_invitations` (project invites)
```
id (uuid)
project_id (uuid) → projects.id
workspace_id (uuid) → organizations.id
email
role (enum: 'project_admin', 'editor', 'commenter', 'viewer')
invited_by (uuid) → users.id
token (unique)
status (enum: 'pending', 'accepted', 'expired')
expires_at
timestamps
```

### Spatie Permission Tables

#### `roles`
```
id (bigint)
workspace_id (char 36, nullable) — the team_foreign_key
name (varchar 65)
guard_name (varchar 65)
timestamps
UNIQUE(workspace_id, name, guard_name)
```

#### `permissions`
```
id (bigint)
name (varchar 65)
guard_name (varchar 65)
timestamps
UNIQUE(name, guard_name)
```

#### `role_has_permissions`
```
permission_id (bigint) → permissions.id
role_id (bigint) → roles.id
PRIMARY KEY(permission_id, role_id)
```

#### `model_has_roles`
```
role_id (bigint) → roles.id
model_type (varchar 125) — 'App\Models\User'
model_id (uuid) — user.id
workspace_id (char 36) — the team_foreign_key
PRIMARY KEY(workspace_id, role_id, model_id, model_type)
```

---

## Role Hierarchy

### Workspace Level (Global Roles)
Stored in `organization_memberships.role`:

- **owner** — created the workspace, full control
- **member** — regular workspace member

These are tracked in your custom table. Spatie roles `workspace_owner` and `workspace_member` exist but are rarely checked directly.

### Project Level (Workspace-Scoped Roles)
Stored in `project_members.role`:

- **project_admin** — full project control (edit, delete, manage members, settings)
- **editor** — can view, comment, edit tasks/project
- **commenter** — can view and comment only
- **viewer** — read-only access

These are **scoped per workspace** via `setPermissionsTeamId($workspace_id)`.

---

## Permission Definitions

All permissions are project-scoped:

| Permission | project_admin | editor | commenter | viewer |
|---|---|---|---|---|
| `project.view` | ✅ | ✅ | ✅ | ✅ |
| `project.comment` | ✅ | ✅ | ✅ | ❌ |
| `project.edit` | ✅ | ✅ | ❌ | ❌ |
| `project.delete` | ✅ | ❌ | ❌ | ❌ |
| `project.manage_members` | ✅ | ❌ | ❌ | ❌ |
| `project.manage_settings` | ✅ | ❌ | ❌ | ❌ |

---

## How It Works: Step-by-Step

### Scenario 1: User Joins Workspace

**Action:** Admin invites `user@example.com` to workspace

**Flow:**
1. `OrganizationInvitationService::createInvitation()` creates record in `organization_invitations`
2. User clicks link → `OrganizationInvitationService::acceptForExistingUser()`
3. Record created in `organization_memberships` with `role = 'member'`
4. Spatie role assigned:
   ```php
   setPermissionsTeamId($organization->id);
   $user->assignRole('workspace_member');
   ```

**Result:**
- `organization_memberships`: `{ user_id: X, organization_id: Y, role: 'member' }`
- `model_has_roles`: `{ model_id: X, role_id: [workspace_member], workspace_id: Y }`

---

### Scenario 2: User Added to Project (Direct)

**Action:** Project admin adds workspace member to project as "editor"

**Flow:**
1. `ProjectMemberController::store()` → `Gate::authorize('addMember', $project)`
2. `ProjectPolicy::addMember()` checks:
   ```php
   setPermissionsTeamId($project->organization_id);
   return $user->can('project.manage_members');
   ```
3. `ProjectMemberService::addMember()` creates record in `project_members`
4. Spatie role assigned via `assignSpatieRole()`:
   ```php
   setPermissionsTeamId($project->organization_id);
   $user->removeRole(['project_admin', 'editor', 'commenter', 'viewer']);
   $user->assignRole('editor');
   ```

**Result:**
- `project_members`: `{ project_id: P, user_id: X, role: 'editor', access_type: 'team_member' }`
- `model_has_roles`: `{ model_id: X, role_id: [editor], workspace_id: Y }`

---

### Scenario 3: User Invited to Project (Email Invitation)

**Action:** Project admin invites `external@example.com` as "project_admin"

**Flow:**
1. `ProjectInvitationService::createInvitation()` creates record in `project_invitations`
   - Stores: `project_id`, `workspace_id`, `email`, `role: 'project_admin'`
2. User clicks link → `ProjectInvitationService::accept()`
3. **Step 1:** Check if user is workspace member
   ```php
   if (!$project->organization->hasMember($user)) {
       $project->organization->addMember($user, 'member');
   }
   ```
   - If not a member → auto-join workspace as `member`
4. **Step 2:** Apply project role (strict override)
   ```php
   setPermissionsTeamId($project->organization_id);
   $user->removeRole(['project_admin', 'editor', 'commenter', 'viewer']);
   $user->assignRole($invitation->role); // 'project_admin'
   ```
5. **Step 3:** Update `project_members` table
   ```php
   $projectMember->update([
       'role' => 'project_admin',
       'access_type' => 'direct_invite',
       'invited_by' => $invitation->invited_by,
   ]);
   ```
6. Mark invitation as accepted: `$invitation->markAccepted()`

**Result:**
- `organization_memberships`: `{ user_id: X, organization_id: Y, role: 'member' }`
- `project_members`: `{ project_id: P, user_id: X, role: 'project_admin', access_type: 'direct_invite' }`
- `model_has_roles`: `{ model_id: X, role_id: [workspace_member, project_admin], workspace_id: Y }`

---

### Scenario 4: Existing Collaborator Receives Invitation (Override)

**Initial State:**
- User is workspace member
- User is project collaborator with role `commenter`

**Action:** Project admin invites same user via email as `project_admin`

**Flow:**
1. Invitation created with `role: 'project_admin'`
2. User accepts → `ProjectInvitationService::accept()`
3. User already in workspace → skip workspace join
4. User already in project → **update path**:
   ```php
   $projectMember->update([
       'role' => 'project_admin',
       'access_type' => 'direct_invite',
       'invited_by' => $invitation->invited_by,
   ]);
   
   setPermissionsTeamId($project->organization_id);
   $user->removeRole(['project_admin', 'editor', 'commenter', 'viewer']);
   $user->assignRole('project_admin');
   ```

**Result:**
- Old role `commenter` is **completely replaced** by `project_admin`
- `access_type` changes from `team_member` to `direct_invite`
- Spatie role in `model_has_roles` updated from `commenter` to `project_admin`

---

## Authorization Flow

### Example: User tries to edit a project

**Request:** `PUT /projects/{project}`

**Flow:**
1. Controller: `Gate::authorize('update', $project)`
2. Laravel resolves `ProjectPolicy::update()`
3. Policy method:
   ```php
   public function update(User $user, Project $project): bool
   {
       setPermissionsTeamId($project->organization_id);
       return $user->can('project.edit');
   }
   ```
4. Spatie checks `model_has_roles` for this user in this workspace
5. Finds role (e.g., `editor`)
6. Checks `role_has_permissions` → `editor` has `project.edit`
7. Returns `true` → request proceeds

**If `setPermissionsTeamId()` was missing:**
- Spatie would check roles across ALL workspaces
- User's `editor` role on Workspace A would grant access to projects in Workspace B
- **This is a critical security bug**

---

## Key Implementation Details

### Why Two Tables?

**Custom tables** (`organization_memberships`, `project_members`):
- Store membership metadata (joined_at, invited_by, access_type)
- Enable efficient queries (who's in this project?)
- Provide audit trail
- Support soft deletes and custom business logic

**Spatie tables** (`model_has_roles`, `role_has_permissions`):
- Handle permission checks (`$user->can('project.edit')`)
- Provide role-permission mapping
- Enable workspace scoping via `team_foreign_key`

### Why Strict Override?

Without strict override, roles would accumulate:
- User invited as `commenter` → has `commenter` role
- User invited again as `project_admin` → now has BOTH roles
- Permission checks become ambiguous

With strict override:
```php
$user->removeRole(['project_admin', 'editor', 'commenter', 'viewer']);
$user->assignRole($newRole);
```
- Only one project role exists at a time per workspace
- Clear, predictable permissions
- Invitation role always wins

### Why `access_type`?

Tracks **how** the user got their role:
- `team_member` — manually added by project admin
- `direct_invite` — joined via email invitation
- `task_assignee` — auto-added when assigned a task

**Use cases:**
- UI: show "Invited" badge vs "Added" badge
- Audit: track invitation-based access
- Business logic: prevent removing users who were explicitly invited

---

## Code Walkthrough

### Adding a Workspace Member

```php
// OrganizationService::addMember()
public function addMember(Organization $organization, User $user, string $role = 'member'): OrganizationMembership
{
    // 1. Create/update record in custom table
    $membership = $organization->memberships()->updateOrCreate(
        ['user_id' => $user->id],
        ['role' => $role, 'joined_at' => now()]
    );

    // 2. Assign Spatie workspace role (scoped to this workspace)
    setPermissionsTeamId($organization->id);
    $user->assignRole("workspace_{$role}");

    return $membership;
}
```

**What happens in the database:**

`organization_memberships`:
```
| id | organization_id | user_id | role   | joined_at |
|----|-----------------|---------|--------|-----------|
| 1  | org-uuid-123    | user-1  | member | 2026-04-01|
```

`model_has_roles`:
```
| role_id | model_type      | model_id | workspace_id |
|---------|-----------------|----------|--------------|
| 5       | App\Models\User | user-1   | org-uuid-123 |
```
(role_id 5 = workspace_member)

---

### Adding a Project Member

```php
// ProjectMemberService::addMember()
public function addMember(Project $project, User $user, ?User $assignedBy, string $role, string $accessType): ProjectMember
{
    return DB::transaction(function () use ($project, $user, $assignedBy, $role, $accessType) {
        // 1. Validate user is workspace member
        $this->validateMemberCanBeAdded($project, $user);

        // 2. Create record in custom pivot table
        $project->members()->attach($user->id, [
            'role'        => $role,
            'access_type' => $accessType,
            'assigned_at' => now(),
            'assigned_by' => $assignedBy->id,
        ]);

        // 3. Assign Spatie project role (workspace-scoped, strict override)
        $this->assignSpatieRole($project, $user, $role);

        return ProjectMember::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->first();
    });
}

private function assignSpatieRole(Project $project, User $user, string $role): void
{
    setPermissionsTeamId($project->organization_id);
    $user->removeRole(['project_admin', 'editor', 'commenter', 'viewer']);
    $user->assignRole($role);
}
```

**What happens in the database:**

`project_members`:
```
| id | project_id | user_id | role   | access_type | assigned_by |
|----|------------|---------|--------|-------------|-------------|
| 1  | proj-123   | user-1  | editor | team_member | user-admin  |
```

`model_has_roles` (updated):
```
| role_id | model_type      | model_id | workspace_id |
|---------|-----------------|----------|--------------|
| 5       | App\Models\User | user-1   | org-uuid-123 | ← workspace_member
| 7       | App\Models\User | user-1   | org-uuid-123 | ← editor (NEW)
```
(role_id 7 = editor, scoped to org-uuid-123)

---

### Accepting a Project Invitation (The Override)

```php
// ProjectInvitationService::accept()
public function accept(string $token, User $user): ProjectMember
{
    $invitation = $this->validateToken($token);
    $project = $invitation->project;

    return DB::transaction(function () use ($invitation, $project, $user) {
        // Step 1: Ensure workspace membership
        if (!$project->organization->hasMember($user)) {
            $project->organization->addMember($user, 'member');
        }

        // Step 2: Apply project role — STRICT OVERRIDE
        $projectMember = ProjectMember::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->first();

        if ($projectMember) {
            // User already in project → UPDATE their role
            $projectMember->update([
                'role'        => $invitation->role,
                'access_type' => 'direct_invite',
                'invited_by'  => $invitation->invited_by,
            ]);

            // Strict override in Spatie
            setPermissionsTeamId($project->organization_id);
            $user->removeRole(['project_admin', 'editor', 'commenter', 'viewer']);
            $user->assignRole($invitation->role);
        } else {
            // User not in project → ADD them
            $this->memberService->addMember(
                $project, $user, User::find($invitation->invited_by),
                $invitation->role, ProjectMember::ACCESS_TYPE_DIRECT_INVITE
            );
        }

        // Step 3: Mark invitation accepted
        $invitation->markAccepted();

        return ProjectMember::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->first();
    });
}
```

**Before invitation:**
```
project_members:
| user_id | role      | access_type |
|---------|-----------|-------------|
| user-1  | commenter | team_member |

model_has_roles:
| model_id | role_id | workspace_id |
|----------|---------|--------------|
| user-1   | 8       | org-123      | ← commenter
```

**After accepting invitation as `project_admin`:**
```
project_members:
| user_id | role          | access_type   | invited_by |
|---------|---------------|---------------|------------|
| user-1  | project_admin | direct_invite | admin-user |

model_has_roles:
| model_id | role_id | workspace_id |
|----------|---------|--------------|
| user-1   | 6       | org-123      | ← project_admin (commenter removed)
```

---

## Authorization Check Flow

### Example: Can user edit project?

**Code:**
```php
Gate::authorize('update', $project);
```

**Execution:**
1. Laravel calls `ProjectPolicy::update($user, $project)`
2. Policy sets workspace scope:
   ```php
   setPermissionsTeamId($project->organization_id);
   ```
3. Policy checks permission:
   ```php
   return $user->can('project.edit');
   ```
4. Spatie queries:
   ```sql
   SELECT * FROM model_has_roles
   WHERE model_id = 'user-1'
     AND model_type = 'App\Models\User'
     AND workspace_id = 'org-uuid-123'
   ```
5. Finds role (e.g., `editor`, role_id = 7)
6. Checks `role_has_permissions`:
   ```sql
   SELECT * FROM role_has_permissions
   WHERE role_id = 7
     AND permission_id IN (SELECT id FROM permissions WHERE name = 'project.edit')
   ```
7. Permission exists → returns `true`

**If `setPermissionsTeamId()` was missing:**
- Query would omit `workspace_id` filter
- User's `editor` role from ANY workspace would match
- **Security breach: cross-workspace access**

---

## Critical Rules

### Rule 1: Always Scope Before Checking
```php
// ❌ WRONG — no scoping
$user->can('project.edit');

// ✅ CORRECT
setPermissionsTeamId($project->organization_id);
$user->can('project.edit');
```

### Rule 2: Strict Override on Role Assignment
```php
// Always remove existing project roles first
$user->removeRole(['project_admin', 'editor', 'commenter', 'viewer']);
$user->assignRole($newRole);
```

### Rule 3: Project Role ≠ Workspace Role
```php
// User can be workspace owner but project viewer
$user->organizationMemberships()->where('organization_id', $org->id)->first()->role; // 'owner'
$user->projectMembers()->where('project_id', $project->id)->first()->role;          // 'viewer'
```

### Rule 4: Track Access Source
```php
// Always set access_type when adding to project
'access_type' => ProjectMember::ACCESS_TYPE_DIRECT_INVITE
```

---

## Common Queries

### Get user's role in a project
```php
$role = ProjectMember::where('project_id', $project->id)
    ->where('user_id', $user->id)
    ->value('role');
```

### Check if user can access project
```php
// Public projects: all workspace members
if ($project->visibility === 'public_to_team') {
    return $project->organization->hasMember($user);
}

// Private projects: explicit members only
return $project->hasMember($user);
```

### Get all projects where user has specific permission
```php
setPermissionsTeamId($workspace->id);
if ($user->can('project.manage_members')) {
    // User has this permission in this workspace
}
```

---

## Troubleshooting

### Issue: User can't access project they should have access to

**Check:**
1. Is user in `organization_memberships` for this workspace?
2. Is project public or is user in `project_members`?
3. Is `setPermissionsTeamId()` called before permission check?
4. Does user have the role in `model_has_roles` with correct `workspace_id`?

### Issue: User has access to projects in wrong workspace

**Cause:** Missing `setPermissionsTeamId()` call

**Fix:** Add before every `hasRole()` / `can()` / `assignRole()` call:
```php
setPermissionsTeamId($project->organization_id);
```

### Issue: Invitation doesn't override existing role

**Check:**
1. Is `removeRole()` called before `assignRole()` in `assignSpatieRole()`?
2. Is the update path in `ProjectInvitationService::accept()` calling the Spatie override?
3. Is `access_type` being updated to `direct_invite`?

---

## Summary

This system provides:
- **Two-level hierarchy:** workspace (owner/member) + project (project_admin/editor/commenter/viewer)
- **Workspace-scoped project roles:** via Spatie's `team_foreign_key` = `workspace_id`
- **Strict role override:** invitation role always wins, no accumulation
- **Independent project permissions:** workspace role doesn't dictate project access
- **Audit trail:** `access_type` tracks how users joined projects
- **Flexible visibility:** public projects visible to all workspace members, private to explicit members only

The key to making it work: **always call `setPermissionsTeamId()` before any Spatie operation**.
