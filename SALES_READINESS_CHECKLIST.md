# Sales Readiness Checklist — Asira (Ticket Management System)

> **How to use this file:** Every item below maps to a real feature or quality gate.
> Mark each item `[x]` when it is fully working end-to-end (backend + frontend + tested manually).
> The product is **ready to sell** when every item in **Part 1 (Must-Have)** is checked.
> Part 2 items are strong differentiators. Part 3 items are post-launch growth features.

---

## Scoring Guide

| Section | Weight | Your Score |
|---|---|---|
| Part 1 — Must-Have (Core Product) | 60 pts | — |
| Part 2 — Should-Have (Competitive) | 30 pts | — |
| Part 3 — Nice-to-Have (Growth) | 10 pts | — |
| **Total** | **100 pts** | — |

**Sales threshold: 80+ pts with all Part 1 items checked.**

---

# PART 1 — MUST-HAVE (Core Product)

These are the features a paying customer expects on day one. Nothing here can be skipped.

---

## 1. Authentication & Onboarding

- [x] User registration with email + password
- [x] Email verification flow (token-based)
- [x] Login with email + password (rate-limited: 5 attempts/min)
- [x] Google OAuth login / registration
- [x] Forgot password → email reset link → reset form
- [x] Password setup for invited users (`must_set_password` flag)
- [x] Onboarding wizard for new users
- [x] Logout
- [x] **INCOMPLETE** — Profile avatar upload (only name update exists; no photo upload)
- [x] Account deletion with email + password confirmation
- [X] **INCOMPLETE** — Email change (currently disabled/read-only in UI)
- [X] **INCOMPLETE** — Session management / active sessions list

**Score: 8/11**

---

## 2. Workspace (Organization) Management

- [x] Create workspace with name, description, color
- [x] Edit workspace details
- [x] Workspace dashboard with overview tab
- [x] Invite members to workspace via email
- [x] Accept workspace invitation (works for both guests and logged-in users)
- [x] Resend / expire / delete invitations
- [x] Member list with roles (owner, member)
- [x] Deactivate / reactivate members
- [x] Role-based access (owner can manage, members cannot)
- [ ] **INCOMPLETE** — Workspace settings page (no dedicated settings route/page)
- [ ] **INCOMPLETE** — Delete workspace (no delete endpoint or UI)
- [ ] **STUB** — "Work", "Messages", "Calendar", "Knowledge" tabs in workspace dashboard show "coming soon" placeholder

**Score: 9/13**

---

## 3. Project Management

- [x] Create project (name, description, color, visibility, dates, manager)
- [x] Edit project general settings
- [x] Project list with pagination and filters
- [x] Project status (on_track, at_risk, off_track, archived)
- [x] Project visibility (public to team / private to members)
- [x] Archive / unarchive project
- [x] Duplicate project
- [x] Delete project (with confirmation)
- [x] Project color picker + icon picker
- [x] Project member management (add, remove, change role)
- [x] Project roles: project_admin, editor, commenter, viewer
- [x] Project invitations with expiration
- [x] Share modal with member list + invite form
- [x] Project activity log
- [x] Project settings page (general, privacy, members, danger zone)
- [X] — Project color/icon visible in sidebar navigation

**Score: 15/17**

---

## 4. Task Management — Core

- [x] Create task (name, description, assignee, status, priority, dates, section)
- [x] Edit task (all fields inline or via detail panel)
- [x] Delete task (soft delete)
- [x] Task status: to_do, in_progress, blocked, in_review, complete
- [x] Task priority: low, medium, high, urgent
- [x] Task visibility: everyone / private
- [x] Start date + due date with date range picker
- [x] Mark task complete / reopen (with completion timestamp + user tracking)
- [x] Duplicate task
- [x] Move task between sections
- [x] Task position / reordering within sections
- [x] Milestone flag
- [x] Task detail panel (slide-in sidebar) with tabs: Details, Comments, Attachments, Activity
- [x] Blocked-by warning modal when completing a task with incomplete dependencies
- [x]  Rich text description (Tiptap is installed but `TaskDetailPanel` uses plain `<textarea>`; `RichEditor.vue` component exists but integration is inconsistent)
- [x] Task due date reminder / overdue visual indicator in task row (overdue styling exists in old views but not consistently in the main `TaskRow` component)

**Score: 14/16**

---

## 5. Task Management — Advanced

- [x] Subtasks (parent-child relationships, subtask list, create subtask)
- [x] Task dependencies (blocked_by / blocks) — add and remove
- [x] Tags / labels per project (create, color, assign to tasks, filter by)
- [x] Custom fields per project (text, number, date, dropdown, multi_select, currency)
- [x] Set custom field values on tasks
- [x] Custom field active/inactive toggle
- [x] Task templates (create, reuse per project)
- [x]  @mentions in task descriptions or comments

**Score: 7/11**

---

## 6. Views

### List View
- [x] Tasks grouped by section (collapsible)
- [x] Inline task creation per section
- [x] Inline date editing (DateRangePicker in row)
- [x] Inline assignee picker in row
- [x] Inline custom field editing in row
- [x] Task completion checkbox in row
- [x] Section add / rename / delete (with move-tasks option)
- [x] Section reordering (drag-and-drop)
- [x] Column visibility toggle (HidePanel)
- [x] Filter panel (status, priority, assignee, tags, due date, custom fields)
- [x] Sort panel (multi-field sorting including custom fields)
- [x] Group panel (by section, assignee, priority, status, custom fields)
- [x] View preferences saved per user/project

### Board View (Kanban)
- [x] Columns by section (not just status — section-based Kanban)
- [x] Task cards with priority, assignee, due date, tags
- [x] Drag-and-drop tasks between columns
- [x] Section reordering
- [x] Add task per column

### Timeline View (Gantt)
- [x] Gantt bars for tasks with start + due date
- [x] Milestone markers (◆) for tasks with only due date
- [x] Drag to resize / move bars (GanttTaskBar, GanttTimeline components exist)
- [x] Task name column on left

### Calendar View
- [x] Monthly calendar grid
- [x] Tasks shown on due date
- [x] Navigate previous/next month
- [x] Click task to open detail panel
- [x] Week view (CalendarWeekView component exists)

### Files View
- [x] FilesView component exists
- [x] FileCard, FileUpload components exist
- [---] **INCOMPLETE** — Verify files view is fully wired to backend (attachments API)

### Dashboard View (per project)
- [x] DashboardView component with widgets
- [x] Task completion widget
- [x] Project status widget
- [x] Tasks by assignee widget
- [x] Tasks by priority widget
- [x] Overdue tasks widget
- [x] Upcoming milestones widget
- [x] Recent activity widget
- [---] **INCOMPLETE** — Dashboard stats endpoint (`/projects/{id}/views/dashboard`) needs verification

**Score: 28/35**

---

## 7. Comments & Collaboration

- [x] Add comment to task
- [x] Edit own comment
- [x] Delete comment (author or admin)
- [x] Emoji reactions on comments
- [x] Attach files to comments
- [x] Paginated comment list (newest first)
- [x] Comment edit tracking (edited_at timestamp)
- [---] **INCOMPLETE** — Comment threads / replies (flat comments only)
- [x] **INCOMPLETE** — @mention users in comments (no mention parsing or notification trigger)
- [x] **INCOMPLETE** — Rich text in comments (Tiptap installed but CommentForm uses plain textarea)

**Score: 7/10**

---

## 8. Attachments

- [x] Upload file attachments to tasks
- [x] Upload file attachments to comments
- [x] Download attachments
- [x] Delete attachments
- [x] Image preview modal (ImagePreviewModal component)
- [x] MIME type tracking
- [x] Soft deletes for audit trail
- [x] **COMPLETE** — File size limit enforcement visible to user (backend validation + UI error feedback)
- [x] **COMPLETE** — Attachment list in task detail shows file type icons

**Score: 9/9**

---

## 9. My Tasks (Personal View)

- [x] My Tasks page (`/my-tasks` route)
- [x] Shows tasks assigned to current user across all projects
- [ ] **INCOMPLETE** — My Tasks UI page (backend exists but no dedicated frontend page; `Pages/Tasks/Index.vue` is a stub)
- [ ] **INCOMPLETE** — Group by project / due date in My Tasks
- [ ] **INCOMPLETE** — My Tasks in sidebar navigation

**Score: 1/5**

---

## 10. Notifications

- [x] Invitation email (queue-based)
- [x] Password reset email
- [x] Email verification email
- [x] Welcome email
- [x] Project invitation email
- [ ] **MISSING** — In-app notification center (no notifications table, no bell icon, no unread count)
- [ ] **MISSING** — Task assignment notification
- [ ] **MISSING** — Comment notification (someone commented on your task)
- [ ] **MISSING** — Due date reminder notification
- [ ] **MISSING** — Notification preferences (opt-in/out per event type)

**Score: 5/10**

---

## 11. Search

- [ ] **MISSING** — Global search bar (no search UI anywhere in the app)
- [ ] **MISSING** — Search tasks by name/description
- [ ] **MISSING** — Search across projects
- [x] User search by name/email (exists for assignee picker — `/api/users/search`)

**Score: 1/4**

---

## 12. User Profile

- [x] View profile (name, email)
- [x] Update name
- [x] Password reset from profile
- [x] Delete account
- [ ] **INCOMPLETE** — Avatar / profile photo upload
- [ ] **INCOMPLETE** — Timezone setting
- [ ] **INCOMPLETE** — Notification preferences

**Score: 4/7**

---

## PART 1 TOTAL SCORE: ~108/148 items complete (~73%)

### Critical Gaps Before Sales (Must Fix)

| # | Gap | Effort | Impact |
|---|---|---|---|
| 1 | **My Tasks page has no frontend** | Medium | High — core personal productivity feature |
| 2 | **In-app notification center** | High | High — users expect to be notified |
| 3 | **Global search** | Medium | High — essential for any task tool |
| 4 | **Rich text in task descriptions** (Tiptap wiring) | Low | Medium — Tiptap already installed |
| 5 | **Workspace delete + settings page** | Low | Medium — basic admin need |
| 6 | **Bulk task operations** | Medium | Medium — power user feature |
| 7 | **@mentions in comments** | Medium | Medium — collaboration quality |
| 8 | **Files view backend wiring** | Low | Medium — verify it works end-to-end |
| 9 | **Dashboard stats endpoint** | Low | Medium — verify it works |
| 10 | **Profile avatar upload** | Low | Low — polish |

---

# PART 2 — SHOULD-HAVE (Competitive Differentiators)

These features separate a basic tool from a competitive product. Aim to have most of these before serious sales.

---

## 13. Real-Time Collaboration

- [ ] **MISSING** — WebSocket / Laravel Echo + Pusher integration
- [ ] **MISSING** — Live task updates (another user edits a task, you see it instantly)
- [ ] **MISSING** — Presence indicators (who is viewing this project right now)
- [ ] **MISSING** — Live comment streaming

**Score: 0/4**

---

## 14. Reporting & Analytics

- [x] Per-project dashboard with widgets (completion, priority, assignee breakdown)
- [x] Project activity log
- [ ] **MISSING** — Workspace-level reporting (across all projects)
- [ ] **MISSING** — Burndown / burnup charts
- [ ] **MISSING** — Velocity tracking
- [ ] **MISSING** — Time tracking (no estimation or actual time fields)
- [ ] **MISSING** — Custom report builder

**Score: 2/7**

---

## 15. Workflow Automation

- [ ] **MISSING** — Automation rules engine (e.g., "when status changes to complete, notify assignee")
- [ ] **MISSING** — Custom status workflows per project
- [ ] **MISSING** — Approval workflows
- [ ] **MISSING** — Recurring tasks

**Score: 0/4**

---

## 16. Advanced Permissions

- [x] Project roles: project_admin, editor, commenter, viewer
- [x] Workspace roles: owner, member
- [x] Access types: direct_invite, team_member, task_assignee
- [x] Workspace member default role on projects
- [ ] **MISSING** — Field-level permissions (hide specific custom fields from certain roles)
- [ ] **MISSING** — Guest access (external collaborators with limited view)

**Score: 4/6**

---

## 17. Integrations

- [x] Google OAuth (authentication only)
- [x] Google Settings admin page (credentials management)
- [ ] **MISSING** — Slack integration (notifications)
- [ ] **MISSING** — GitHub / GitLab integration (link commits to tasks)
- [ ] **MISSING** — Zapier / Make webhook support
- [ ] **MISSING** — Calendar sync (Google Calendar / iCal export)
- [ ] **MISSING** — REST API with API keys (for external integrations)

**Score: 2/7**

---

## 18. Data Management

- [ ] **MISSING** — CSV import for tasks
- [ ] **MISSING** — CSV / Excel export for tasks
- [ ] **MISSING** — Project export (full project backup)
- [ ] **MISSING** — Data retention policy settings

**Score: 0/4**

---

## PART 2 TOTAL SCORE: ~8/32 items complete (~25%)

---

# PART 3 — NICE-TO-HAVE (Growth Features)

Post-launch features that drive expansion revenue and retention.

---

## 19. Portfolio / Cross-Project

- [ ] Portfolio view (all projects in one timeline)
- [ ] Cross-project task dependencies
- [ ] Resource allocation / workload view
- [ ] Program / Epic management

---

## 20. Advanced Collaboration

- [ ] Workspace-level messaging / chat
- [ ] Document / wiki / knowledge base
- [ ] Video call integration
- [ ] Guest / client portal

---

## 21. Mobile

- [ ] Responsive mobile web design (verify on mobile viewport)
- [ ] Progressive Web App (PWA) manifest
- [ ] Native iOS app
- [ ] Native Android app

---

## 22. Enterprise

- [ ] SSO / SAML integration
- [ ] Audit log export
- [ ] Custom branding / white-label
- [ ] SLA tracking
- [ ] IP allowlist / security policies
- [ ] GDPR data export / right to erasure

---

## 23. Billing & Subscriptions

- [ ] Pricing tiers (Free / Pro / Business)
- [ ] Stripe / payment integration
- [ ] Usage limits per plan (seats, projects, storage)
- [ ] Billing portal / invoice history
- [ ] Trial expiry flow

---

# INFRASTRUCTURE & QUALITY GATES

These are not features but are required before any paying customer goes live.

---

## 24. Performance & Scalability

- [ ] **VERIFY** — N+1 query audit (eager loading on task/project queries with members, tags, custom fields)
- [ ] **MISSING** — Redis caching for frequently read data (project members, custom fields)
- [ ] **MISSING** — Database query optimization (EXPLAIN on heavy queries)
- [ ] **MISSING** — Pagination on all list endpoints (tasks, comments, activities)
- [ ] **VERIFY** — Queue worker configured and running (email jobs use queue)
- [ ] **MISSING** — CDN for file attachments (currently local storage)
- [ ] **MISSING** — Rate limiting on API endpoints (only login has throttle)

---

## 25. Security

- [ ] **VERIFY** — All routes behind auth middleware (no unauthenticated data leaks)
- [ ] **VERIFY** — Authorization checks in every controller (user can only access their org's data)
- [ ] **VERIFY** — File upload validation (MIME type, size limits enforced server-side)
- [ ] **MISSING** — CSRF protection on all state-changing API calls (currently relies on session cookie — verify `X-CSRF-TOKEN` header is sent on all fetch calls)
- [ ] **MISSING** — Input sanitization on rich text fields (XSS prevention)
- [ ] **MISSING** — SQL injection audit (Eloquent used throughout — verify no raw queries)
- [ ] **MISSING** — Secrets not committed to git (check `.env` is in `.gitignore`)

---

## 26. Error Handling & UX

- [ ] **INCOMPLETE** — Global error boundary / 404 / 500 pages
- [ ] **INCOMPLETE** — Form validation error messages shown consistently across all forms
- [ ] **VERIFY** — Toast notifications work on all success/error actions
- [ ] **MISSING** — Loading skeletons on initial page load (LoadingSkeleton component exists but usage is inconsistent)
- [ ] **MISSING** — Empty state illustrations (EmptyState component exists — verify it's used everywhere)
- [ ] **MISSING** — Offline / network error handling

---

## 27. Testing

- [ ] **MISSING** — Feature tests (tests/Feature/ is empty — only a Properties/ stub exists)
- [ ] **MISSING** — Unit tests for service layer
- [ ] **MISSING** — Frontend component tests
- [ ] **MISSING** — End-to-end tests (Cypress / Playwright)
- [ ] **MISSING** — CI pipeline (GitHub Actions / similar)

---

## 28. Deployment & DevOps

- [ ] **MISSING** — Production `.env` configuration documented
- [ ] **MISSING** — Docker / deployment guide
- [ ] **MISSING** — Database backup strategy
- [ ] **MISSING** — Health check endpoint (`/health`)
- [ ] **MISSING** — Error monitoring (Sentry / Bugsnag)
- [ ] **MISSING** — Uptime monitoring
- [ ] **MISSING** — Log aggregation (Papertrail / Logtail)

---

## 29. Documentation

- [ ] **MISSING** — User-facing help docs / onboarding tooltips
- [ ] **MISSING** — API documentation (Swagger / Postman collection)
- [ ] **MISSING** — Deployment / self-hosting guide
- [ ] **MISSING** — Changelog

---

## 30. Landing Page & Marketing

- [x] Landing page exists (`Landing.vue`) with hero, features, use cases, testimonials, pricing section
- [ ] **INCOMPLETE** — Product screenshots / mockups (hero shows placeholder "Replace with actual screenshot")
- [ ] **INCOMPLETE** — Pricing section has no real pricing tiers or CTA
- [ ] **MISSING** — Terms of Service page
- [ ] **MISSING** — Privacy Policy page
- [ ] **MISSING** — Cookie consent banner (required for GDPR)
- [ ] **MISSING** — Contact / support page

---

# SUMMARY SCORECARD

## What's Working Well ✅

The core of the product is solid. The backend architecture is clean (service layer, thin controllers, proper migrations, soft deletes, UUID PKs). The frontend component library is well-organized with composables for filters, sort, grouping, and optimistic UI. The four main views (List, Board, Timeline, Calendar) are implemented. Custom fields, tags, subtasks, dependencies, comments, and attachments all have working backends.

## What Blocks Sales 🚫

| Priority | Item | Status |
|---|---|---|
| 🔴 P0 | My Tasks page (frontend missing) | Backend exists, no UI |
| 🔴 P0 | In-app notification center | Completely missing |
| 🔴 P0 | Global search | Completely missing |
| 🔴 P0 | No automated tests | Empty test suite |
| 🔴 P0 | Security audit (auth checks, XSS, CSRF) | Unverified |
| 🟠 P1 | Rich text in task descriptions | Tiptap installed, not wired |
| 🟠 P1 | Workspace delete + settings | Missing |
| 🟠 P1 | Bulk task operations | Missing |
| 🟠 P1 | CSV export | Missing |
| 🟠 P1 | Landing page screenshots | Placeholders only |
| 🟠 P1 | Terms of Service + Privacy Policy | Missing (legal requirement) |
| 🟠 P1 | Error monitoring + deployment docs | Missing |
| 🟡 P2 | Real-time collaboration (WebSockets) | Missing |
| 🟡 P2 | @mentions in comments | Missing |
| 🟡 P2 | Billing / subscription system | Missing |

## Estimated Effort to Sales-Ready

| Work Area | Estimated Days |
|---|---|
| My Tasks frontend page | 1–2 days |
| In-app notifications (basic) | 3–5 days |
| Global search (basic, DB-level) | 2–3 days |
| Rich text wiring (Tiptap) | 1 day |
| Workspace settings + delete | 1 day |
| Bulk task operations | 2–3 days |
| CSV export | 1–2 days |
| Security audit + fixes | 3–5 days |
| Landing page polish + legal pages | 1–2 days |
| Basic feature tests | 3–5 days |
| **Total** | **~18–33 days** |

---

*Last reviewed: May 2026 — based on full codebase audit of Laravel 12 + Vue 3 + Inertia.js stack.*
