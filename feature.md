# Product Readiness Checklist — Asira (Asana Clone)

## Tech Stack
- **Backend:** Laravel 12, PHP 8.2, MySQL
- **Frontend:** Vue 3 + Inertia.js, Tailwind CSS v4, Pinia, Vite
- **Auth:** Laravel Passport (OAuth2), Spatie Permissions (RBAC)
- **Editor:** TipTap v3 (rich text, mentions, task lists)
- **Charts:** Chart.js 4.4

---

## ✅ Ready / Complete

### Authentication & Security
- [x] Email/password registration & login
- [x] Google OAuth login
- [x] Magic link / auto-login via token
- [x] Email verification flow
- [x] Password reset (email-based)
- [x] "Must set password" flow for invited users
- [x] Login rate limiting (5 attempts/min)
- [x] Account suspension
- [x] Session management (database driver)

### Workspace (Organization) Management
- [x] Create / update / delete workspaces
- [x] Workspace member roles: owner, admin, member, guest
- [x] Invite users to workspace (email invite flow)
- [x] Accept workspace invitation
- [x] Switch active workspace
- [x] Workspace dashboard

### Project Management
- [x] Create / edit / delete / archive projects
- [x] Project color & icon customization
- [x] Project status: on_track, at_risk, off_track, archived
- [x] Project visibility: public to team / private to members
- [x] Duplicate project
- [x] Project manager / owner assignment
- [x] Project activity / audit log
- [x] Project status update text field

### Project Members & Invitations
- [x] Add/remove project members
- [x] Project roles: project_admin, editor, viewer, commenter
- [x] Invite users to project (email invite with token)
- [x] Accept project invitation

### Task Management
- [x] Create / edit / delete tasks
- [x] Task status: to_do, in_progress, blocked, in_review, complete
- [x] Task priority: low, medium, high, urgent
- [x] Task assignee
- [x] Task due date & start date
- [x] Task description (rich text with TipTap)
- [x] Mark task complete / reopen
- [x] Duplicate task
- [x] Move task (between sections/projects)
- [x] Subtasks (nested tasks, unlimited depth)
- [x] Task dependencies (blocked_by / blocks)
- [x] Task milestones
- [x] Task visibility (everyone / private)
- [x] Task position ordering (drag & drop)
- [x] Task activity / field-level audit log
- [x] Task templates (reusable blueprints)

### Project Views
- [x] List view (with column visibility toggle)
- [x] Board view (Kanban, drag & drop)
- [x] Timeline view (start/due date bars)
- [x] Calendar view (monthly, task dots)
- [x] Files view (attachments browser)
- [x] Dashboard view (project stats)

### Sections (Columns)
- [x] Create / edit / delete sections per project
- [x] Reorder sections
- [x] My Tasks personal sections

### My Tasks (Personal Inbox)
- [x] Personal task list (cross-project)
- [x] Personal sections with ordering
- [x] View preferences (sort, group, filter)
- [x] Auto-assign to My Tasks when assigned to user

### Comments
- [x] Create / edit / delete comments on tasks
- [x] Rich text comments (TipTap)
- [x] @mention users in comments
- [x] Emoji reactions on comments
- [x] Comment attachments

### Attachments
- [x] Upload files to tasks and comments
- [x] Download attachments
- [x] Mime type tracking

### Tags
- [x] Create / edit / delete tags per project
- [x] Assign multiple tags to tasks

### Custom Fields
- [x] Field types: text, number, select, multiselect, checkbox, date, currency
- [x] Workspace-scoped and personal-scoped fields
- [x] Custom field values per task
- [x] Reorder fields
- [x] Soft-delete (is_active flag)

### Notifications (Inbox)
- [x] In-app notifications
- [x] Mention in task description
- [x] Mention in comment
- [x] Task assigned notification
- [x] Mark as read / unread
- [x] Mark all as read
- [x] Unread count badge
- [x] Delete notification

### Global Search
- [x] Search across tasks, projects, users

### Reports
- [x] Reporting dashboard (Chart.js)
- [x] Project metrics / stats

### Onboarding
- [x] Onboarding wizard for new users
- [x] Demo workspace auto-setup via OnboardingDataService

### External API (V1)
- [x] API token management (create/revoke tokens)
- [x] Full REST API for workspaces, projects, tasks, comments, attachments, users
- [x] SSO / JWT token exchange (third-party integration)
- [x] Google OAuth settings
- [x] Scramble (auto-generated API docs)

### Architecture
- [x] Service layer pattern (28 service classes)
- [x] Thin controllers
- [x] Query scopes on models
- [x] Event-driven email (queued jobs)
- [x] Activity logging throughout
- [x] UUID primary keys
- [x] Soft deletes on critical models
- [x] Timezone support (per-user UTC offset)
- [x] Pagination support

---

## ❌ Missing / Incomplete — Must Address Before Sale

### 1. Real-Time Collaboration ⚠️ HIGH PRIORITY
- [ ] No WebSocket/live updates — Pusher or Laravel Reverb not configured
- Users won't see other people's changes without a page refresh. For a team tool, this is a noticeable gap.

### 2. Automated Testing ⚠️ MEDIUM PRIORITY
- [ ] Only 5 unit test files exist (AttachmentService, AuthService, InvitationModel, UserModel, Example)
- [ ] Zero feature/integration tests for controllers, task flows, or API endpoints
- This is a risk for client confidence and long-term maintenance.

### 3. Bulk Operations ⚠️ MEDIUM PRIORITY
- [ ] No bulk assign, bulk status change, bulk delete, or bulk move for tasks
- Asana has this — clients will ask for it.

### 4. Recurring Tasks
- [ ] No recurring task functionality (daily, weekly, monthly)

### 5. Export / Import
- [ ] No CSV/Excel export of tasks or projects
- [ ] No project import

### 6. Webhooks
- [ ] No outbound webhook system for integrations (Slack, Zapier, etc.)

### 7. Dark Mode
- [ ] Tailwind is configured but no dark mode toggle exists in the UI

### 8. Mobile Responsiveness
- [ ] Tailwind classes are used, but complex views (Timeline, Board) need manual verification on mobile

### 9. Timeline View Depth
- [ ] Timeline exists but appears basic (horizontal bars only) — no drag-to-resize, no dependency lines on timeline

### 10. Rate Limiting on API
- [ ] Only auth routes are rate-limited; the internal API and V1 API endpoints have no throttle middleware

### 11. File Storage in Production
- [ ] Currently set to `FILESYSTEM_DISK=local` — no S3/cloud storage configured for scalable file handling

### 12. Cache Driver
- [ ] Using database as cache driver (fine for dev) — needs Redis for production scale

### 13. Queue Driver
- [ ] Using database queue — needs Redis/SQS for production scale

### 14. Email in Production
- [ ] `.env.example` shows Gmail SMTP — needs a transactional email service (SendGrid, Mailgun, SES)

---

## Feature List for Client Discussion

Here's everything you can sell:

**Workspace & Team**
- Multi-workspace support with roles (owner, admin, member, guest)
- Email invite flow for workspace and project members
- Member management with role-based permissions

**Projects**
- Create projects with color, icon, status, visibility, manager
- Archive and duplicate projects
- Project-level member roles (admin, editor, viewer, commenter)
- Project activity audit log
- Status updates (on track / at risk / off track)

**Task Management**
- Full task CRUD with rich text descriptions
- Status workflow (to_do → in_progress → blocked → in_review → complete)
- 4-level priority (low, medium, high, urgent)
- Assignee, start date, due date
- Subtasks (nested, unlimited levels)
- Task dependencies (blocking/blocked-by)
- Milestones
- Task templates (reusable blueprints)
- Duplicate & move tasks across projects
- Drag-and-drop reordering

**5 Project Views**
- List view (spreadsheet-style with column visibility)
- Board view (Kanban with drag-and-drop)
- Timeline view (Gantt-style bars)
- Calendar view (monthly calendar)
- Files view (attachment browser)
- Dashboard view (project stats & charts)

**My Tasks**
- Personal cross-project task inbox
- Custom personal sections
- View preferences (sort, group, filter) saved per user

**Custom Fields**
- 7 field types: text, number, date, checkbox, select, multiselect, currency
- Workspace-scoped or personal-scoped
- Per-task custom field values

**Collaboration**
- Threaded comments with rich text
- @mention users in tasks and comments
- Emoji reactions on comments
- File attachments on tasks and comments

**Notifications**
- In-app notification inbox
- Notifications for: mentions, task assignments
- Mark read/unread, delete, mark all read

**Search**
- Global search across tasks, projects, users

**Reporting**
- Project analytics dashboard with charts
- Task completion stats

**Authentication**
- Email/password with email verification
- Google OAuth (social login)
- Magic link login
- Password reset
- Account suspension

**Developer / Integration**
- External REST API (v1) with token authentication
- SSO / JWT token exchange
- Auto-generated API documentation (Scramble)
- Google OAuth settings

**Architecture (for technical clients)**
- Laravel 12 + Vue 3 + Inertia.js
- UUID keys, soft deletes, full audit trails
- Queue-based email delivery
- Scalable service-layer architecture