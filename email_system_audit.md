# Email System Audit & Architecture Review

## Phase 1 & 2 – Inventory and Delivery Methods

Here is the inventory of all emails sent by the application along with their triggers and delivery methods.

| Email / Feature | File Location | Class / Mechanism | Trigger | Delivery Method | Queue? | Queue Name |
|---|---|---|---|---|---|---|
| **Welcome Email** | `app/Mail/WelcomeEmail.php` | Mailable | User Registration (`SendWelcomeEmailListener`) | `Mail::send()` inside a Queued Listener | ✅ Yes | default |
| **User Invitation** | `app/Jobs/SendInvitationEmail.php` | Mailable (`InvitationMail`) | Event handled by `SendInvitationEmailListener` | Job (`ShouldQueue`) | ✅ Yes (Double) | default |
| **Workspace Invitation** | `app/Helpers/MailHelper.php` | Mailable (`InvitationMail`) | Call to `MailHelper::sendInvitation()` | `Mail::to()->queue()` | ✅ Yes | default |
| **Project Invitation** | `app/Helpers/MailHelper.php` | Mailable (`ProjectInvitationMail`) | Call to `MailHelper::sendProjectInvitation()` | `Mail::to()->queue()` | ✅ Yes | default |
| **Password Reset** | `app/Jobs/SendPasswordResetEmail.php` | Mailable (`ResetPasswordEmail`) | Event handled by `SendPasswordResetEmailListener` | Job (`ShouldQueue`) | ✅ Yes (Double) | default |
| **Password Changed** | `app/Http/Controllers/AuthController.php` | Mailable (`PasswordChangedEmail`) | Password Update (`resetPassword`) | `Mail::to()->queue()` | ✅ Yes | default |
| **Email Verification** | `app/Jobs/SendVerificationEmail.php` | Mailable (`VerificationEmail`) | `EmailVerificationService` | Job (`ShouldQueue`) | ✅ Yes | default |
| **Contact Admin** | `app/Http/Controllers/ContactController.php` | `Mail::raw()` | Contact Form Submission | `Mail::raw()` | ❌ No | - |
| **Contact Confirmation**| `app/Http/Controllers/ContactController.php` | `Mail::raw()` | Contact Form Submission | `Mail::raw()` | ❌ No | - |
| **SMTP Test** | `app/Services/MailTestService.php` | Symfony Transport | Admin Settings SMTP Test | Custom Mailer | ❌ No | - |

---

## Phase 3 – Queue Architecture Review

### Expected Queue Connection
The `.env` file indicates that `QUEUE_CONNECTION=database` is expected.

### Queue Implementation Details
- **Job Dispatching**: Most transactional emails (Welcome, Resets, Verifications) correctly use queues either by dispatching jobs or using `ShouldQueue` on listeners/mailables.
- **Unnecessarily Synchronous**: The contact form emails in `ContactController` are fully synchronous. If the SMTP server is slow, this will result in slow response times for users submitting the form, potentially causing a timeout.
- **Duplicate/Double Queuing**: 
  - `SendInvitationEmailListener` implements `ShouldQueue` and dispatches `SendInvitationEmail` which **also** implements `ShouldQueue`.
  - `SendPasswordResetEmailListener` implements `ShouldQueue` and dispatches `SendPasswordResetEmail` which **also** implements `ShouldQueue`.
  This means the listener is queued, and when executed, it just queues another job, adding unnecessary delay.
- **Queue Names**: Everything uses the `default` queue. No specific queue names (like `emails` or `notifications`) are being used, which prevents prioritizing specific jobs.
- **Failed Jobs & Retries**: Jobs do not explicitly define `$tries` or `$backoff` variables, meaning they rely on global queue worker configurations.

---

## Phase 4 – Current State

- **Total number of email types:** 8 main features (+1 Admin test)
- **Number sent immediately:** 2 (Contact form emails)
- **Number sent through queues:** 6
- **Number using Notifications:** 0
- **Number using Mailables:** 6
- **Number using Jobs:** 3 explicit jobs

---

## Phase 5 – Problems Found & Recommendations

### Critical Issues Found
> [!WARNING]
> **Constructor Mismatch in InvitationMail**
> `App\Mail\InvitationMail` expects a single `OrganizationInvitation` parameter in its constructor. However, `App\Jobs\SendInvitationEmail` calls `new InvitationMail($this->user, $acceptanceUrl, $this->invitation->expires_at)`. This will cause a runtime error when the job runs.

> [!WARNING]
> **Double Queuing**
> Due to listeners and their dispatched jobs both implementing `ShouldQueue`, some emails are being queued twice.

### Recommendations & Answers to Questions

1. **Which emails should remain synchronous?**
   None, except for the Admin SMTP Test (`MailTestService`), which must return an immediate response. All user-facing emails should be queued to prevent blocking the request.

2. **Which emails should always be queued?**
   All of them. Specifically, `ContactController` should be refactored to queue its `Mail::raw()` calls, or better yet, use Mailables/Notifications.

3. **Are we following Laravel best practices?**
   Partially. 
   - **Good:** Using queues for transactional emails.
   - **Bad:** Hardcoding `Mail::raw()` inside controllers.
   - **Bad:** Using `Mailables` wrapped inside `Jobs` wrapped inside `Listeners`. 
   - **Best Practice Missing:** Laravel recommends using **Notifications** (`Illuminate\Notifications\Notification`) for things like welcomes, verifications, resets, and invitations. Notifications can be queued effortlessly (`ShouldQueue`) and can be routed to multiple channels (Email, DB, SMS) later if needed.

4. **Are there unnecessary delays?**
   Yes, the double-queuing of jobs inside queued listeners creates unnecessary delays and extra database inserts for jobs.

5. **Are there performance issues?**
   Synchronous execution in `ContactController` is a significant performance bottleneck waiting to happen.

6. **Are there scalability concerns?**
   Using the `default` queue for everything means slow tasks (like image processing) could block time-sensitive emails (like password resets) from being sent quickly.

### Final Proposed Architecture

1. **Migrate to Notifications:** Refactor Mailables and Jobs into Laravel Notifications (e.g., `WelcomeNotification`, `ResetPasswordNotification`, `InvitationNotification`).
2. **Centralized Queuing:** Implement `ShouldQueue` directly on the Notifications and use the `->notify()` method on the User model. Drop the redundant Jobs and Listeners where possible.
3. **Dedicated Queue:** Assign all email notifications to a dedicated queue (e.g., `php artisan queue:work --queue=high,emails,default`) so that password resets aren't delayed by low-priority background tasks.
4. **Fix Bugs:** Resolve the constructor mismatch for `InvitationMail` immediately.
5. **Controller Cleanup:** Move the email logic out of `ContactController` into a queued Mailable or Event/Listener structure.
