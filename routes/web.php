<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminWorkspaceController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\GoogleSettingsController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationInvitationController;
use App\Http\Controllers\OrganizationMemberController;
use App\Http\Controllers\PendingMemberController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectInvitationController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\ProjectSettingsController;
use App\Http\Controllers\ProjectActivityController;
use App\Http\Controllers\ProjectViewController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WorkspaceDashboardController;
use App\Http\Controllers\PrivateNoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\LandingController::class, 'index'])->name('landing');

// Static pages
Route::get('/about', function () {
    return inertia('About');
})->name('about');

Route::get('/contact', function () {
    return inertia('Contact');
})->name('contact');

// Contact form submission (public, with CSRF protection)
Route::post('/api/contact', [App\Http\Controllers\ContactController::class, 'store'])->middleware(['web'])->name('contact.store');

Route::get('/privacy', function () {
    return inertia('Privacy');
})->name('privacy');

Route::get('/terms', function () {
    return inertia('Terms');
})->name('terms');

Route::get('/security', function () {
    return inertia('Security');
})->name('security');

Route::get('/gdpr', function () {
    return inertia('GDPR');
})->name('gdpr');

// Authenticated routes
Route::middleware(['auth', 'password.set'])->group(function () {
    Route::middleware('check.subscription')->group(function () {
        Route::get('/dashboard', function () {
            $user = auth()->user();
            
            // Get user's workspaces
            $workspaces = $user->organizations()
                ->where('organizations.is_active', true)
                ->whereNull('organization_memberships.deleted_at')
                ->get()
                ->map(function ($workspace) {
                    return [
                        'id' => $workspace->id,
                        'name' => $workspace->name,
                        'description' => $workspace->description,
                        'avatar_color' => $workspace->avatar_color,
                        'members_count' => $workspace->members()->count(),
                    ];
                });
            
            // If user has no workspaces, check if they have project access
            if ($workspaces->isEmpty()) {
                $projectCount = \App\Models\Project::visibleTo($user)->count();
                if ($projectCount > 0) {
                    return redirect()->route('projects.index');
                }
                return redirect('/onboarding');
            }
            
            $projects = \App\Models\Project::visibleTo($user)
                ->select('id', 'name', 'color', 'icon', 'privacy', 'created_at', 'archived_at')
                ->with('members')
                ->limit(6)
                ->get()
                ->map(function ($project) {
                    return [
                        'id' => $project->id,
                        'name' => $project->name,
                        'color' => $project->color ?: '#6366f1', // Default color
                        'icon' => $project->icon,
                        'privacy' => $project->privacy,
                        'members_count' => $project->members->count(),
                        'archived_at' => $project->archived_at,
                    ];
                });

            return inertia('Dashboard', [
                'workspaces' => $workspaces,
                'projects' => $projects,
                'userRole' => 'member',
                'workspace' => null,
                'userWorkspaces' => $workspaces,
            ]);
        })->name('dashboard');

        // My Tasks routes
        Route::get('/my-tasks', [\App\Http\Controllers\MyTasksController::class, 'index'])->name('my-tasks.index');
        Route::get('/my-tasks/api/tasks', [\App\Http\Controllers\MyTasksController::class, 'getTasks'])->name('my-tasks.api.tasks');
        Route::post('/my-tasks/api/tasks', [\App\Http\Controllers\MyTasksController::class, 'storeTask'])->name('my-tasks.api.tasks.store');
        Route::post('/my-tasks/api/preferences', [\App\Http\Controllers\MyTasksController::class, 'saveViewPreferences'])->name('my-tasks.api.preferences.save');
        Route::get('/my-tasks/api/preferences/{viewType}', [\App\Http\Controllers\MyTasksController::class, 'getViewPreferences'])->name('my-tasks.api.preferences.get');
        // My Tasks section management
        Route::get('/my-tasks/api/sections', [\App\Http\Controllers\MyTasksController::class, 'getSections'])->name('my-tasks.api.sections.index');
        Route::post('/my-tasks/api/sections', [\App\Http\Controllers\MyTasksController::class, 'storeSection'])->name('my-tasks.api.sections.store');
        Route::put('/my-tasks/api/sections/{sectionId}', [\App\Http\Controllers\MyTasksController::class, 'updateSection'])->name('my-tasks.api.sections.update');
        Route::delete('/my-tasks/api/sections/{sectionId}', [\App\Http\Controllers\MyTasksController::class, 'destroySection'])->name('my-tasks.api.sections.destroy');
        Route::post('/my-tasks/api/sections/reorder', [\App\Http\Controllers\MyTasksController::class, 'reorderSections'])->name('my-tasks.api.sections.reorder');

        // Inbox route
        Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');

        // Reporting Dashboard
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/data', [ReportController::class, 'getData'])->name('reports.data');
        Route::get('/reports/assignees', [ReportController::class, 'getAssignees'])->name('reports.assignees');
    });

    Route::get('/onboarding', function () {
        return inertia('Auth/OnboardingWizard');
    })->name('onboarding');
    
    Route::post('/onboarding/complete', [AuthController::class, 'completeOnboarding'])->name('onboarding.complete');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

    // Google OAuth routes
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])
        ->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
        ->name('auth.google.callback');
});

// Single entry point for all invitations (works for both guest and authenticated users)
Route::get('/invitation/accept', [\App\Http\Controllers\InvitationAcceptController::class, 'accept'])
    ->name('invitation.accept');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/auth/login/{token}', [AuthController::class, 'autoLogin'])->name('auth.auto-login');

// Password setup route (for new users created via invitation)
Route::middleware('auth')->group(function () {
    Route::get('/set-password', [AuthController::class, 'showSetPassword'])->name('password.set');
    Route::post('/set-password', [AuthController::class, 'savePassword'])->name('password.save');
});

// Organization invitation routes (authenticated)
Route::middleware(['auth', 'password.set'])->group(function () {
    Route::post('/invitations', [OrganizationInvitationController::class, 'store'])->name('invitations.store');
    Route::post('/invitations/{token}/accept', [OrganizationInvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('/invitations/{invitation}/resend', [OrganizationInvitationController::class, 'resend'])->name('invitations.resend');
    Route::delete('/invitations/{invitation}', [OrganizationInvitationController::class, 'destroy'])->name('invitations.destroy');
});

// API routes for fetching data
Route::middleware(['auth', 'password.set'])->group(function () {
    Route::get('/api/organizations/{organization}/invitations', [OrganizationInvitationController::class, 'listPending'])->name('api.invitations.pending');
    Route::post('/api/invitations/{invitation}/expire', [OrganizationInvitationController::class, 'expire'])->name('api.invitations.expire');
    Route::delete('/api/invitations/{invitation}', [OrganizationInvitationController::class, 'destroy'])->name('api.invitations.delete');
    Route::post('/api/organization-memberships/{membership}/deactivate', [OrganizationMemberController::class, 'deactivate'])->name('api.memberships.deactivate');
    Route::post('/api/organization-memberships/{membership}/activate', [OrganizationMemberController::class, 'activate'])->name('api.memberships.activate');
});

// Profile routes
Route::middleware(['auth', 'password.set'])->group(function () {
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile.show');
    Route::put('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/upload-avatar', [AuthController::class, 'uploadAvatar'])->name('profile.upload-avatar');
    Route::delete('/profile/remove-avatar', [AuthController::class, 'removeAvatar'])->name('profile.remove-avatar');
    Route::post('/profile/change-password', [AuthController::class, 'changePassword'])->name('profile.change-password');
    Route::delete('/profile/delete', [AuthController::class, 'deleteAccount'])->name('profile.delete');
    
    // Settings routes
    Route::get('/settings', function () {
        $user = auth()->user();
        $userWorkspaces = $user->getAccessibleOrganizations()
            ->map(function ($organization) use ($user) {
                return [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'avatar_color' => $organization->avatar_color,
                    'description' => $organization->description,
                    'role' => $user->getWorkspaceRole($organization->id),
                    'is_owner' => $user->isWorkspaceOwner($organization->id),
                    'is_admin' => $user->isWorkspaceAdmin($organization->id),
                    'is_member' => $user->isWorkspaceMember($organization->id),
                ];
            });
        
        return inertia('Settings', [
            'userWorkspaces' => $userWorkspaces,
        ]);
    })->name('settings');
    Route::post('/settings/save', [AuthController::class, 'saveSettings'])->name('settings.save');

    // Subscription routes
    Route::get('/settings/subscriptions', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::post('/settings/subscriptions/change-plan', [SubscriptionController::class, 'changePlan'])->name('subscriptions.change-plan');
    Route::post('/settings/subscriptions/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::post('/settings/subscriptions/rebuy', [SubscriptionController::class, 'rebuy'])->name('subscriptions.rebuy');
    Route::get('/subscription/checkout-session', [SubscriptionController::class, 'getCheckoutSession'])->name('subscription.checkout-session');
    Route::get('/subscription/payment-success', [SubscriptionController::class, 'paymentSuccess'])->name('subscription.payment-success');

    // API Token routes (workspace owner/admin or global admin)
    Route::get('/settings/integrations/tokens', [ApiTokenController::class, 'show'])->name('settings.api-tokens.show');
    Route::post('/settings/integrations/tokens', [ApiTokenController::class, 'store'])->name('settings.api-tokens.store');
    Route::delete('/settings/integrations/tokens/{tokenId}', [ApiTokenController::class, 'destroy'])->name('settings.api-tokens.destroy');
    Route::delete('/settings/integrations/tokens/{tokenId}/delete', [ApiTokenController::class, 'delete'])->name('settings.api-tokens.delete');
    Route::post('/settings/integrations/tokens/revoke-all', [ApiTokenController::class, 'revokeAll'])->name('settings.api-tokens.revoke-all');
    Route::get('/api/tokens', [ApiTokenController::class, 'list'])->name('api.tokens.list');
});

// Email verification routes
Route::middleware(['auth', 'password.set'])->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'show'])->name('verification.notice');
    Route::post('/email/verify', [EmailVerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/email/resend', [EmailVerificationController::class, 'resend'])->name('verification.send');
});

// Authenticated routes (organization, projects, etc.)
Route::middleware(['auth', 'password.set'])->group(function () {
    // Organization routes
    Route::resource('organizations', OrganizationController::class);
    Route::resource('organizations.members', OrganizationMemberController::class, [
        'parameters' => ['member' => 'user']
    ]);

    // Workspace dashboard routes
    Route::middleware('check.subscription')->group(function () {
        Route::get('/workspace/{organization}/dashboard', [WorkspaceDashboardController::class, 'show'])->name('workspace.dashboard');
        Route::put('/workspace/{organization}', [WorkspaceDashboardController::class, 'updateWorkspace'])->name('workspace.update');
        Route::post('/workspace/{organization}/switch', [WorkspaceDashboardController::class, 'switchWorkspace'])->name('workspace.switch');
        Route::delete('/workspace/{organization}', [WorkspaceDashboardController::class, 'destroy'])->name('workspace.destroy');
    });

    // Project routes - with subscription check
    Route::middleware('check.subscription')->group(function () {
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
        Route::put('/projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.updateStatus');
        Route::get('/projects/{project}/share-data', [ProjectController::class, 'shareData'])->name('projects.shareData');
        Route::put('/projects/{project}/visibility', [ProjectController::class, 'updateVisibility'])->name('projects.updateVisibility');
        Route::put('/projects/{project}/workspace-member-role', [ProjectController::class, 'updateWorkspaceMemberRole'])->name('projects.updateWorkspaceMemberRole');
        Route::put('/projects/{project}/lead', [ProjectController::class, 'changeProjectLead'])->name('projects.changeProjectLead');

        // Project member routes
        Route::get('/projects/{project}/members', [ProjectMemberController::class, 'index'])->name('projects.members.index');
        Route::post('/projects/{project}/members', [ProjectMemberController::class, 'store'])->name('projects.members.store');
        Route::delete('/projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy'])->name('projects.members.destroy');
        Route::patch('/projects/{project}/members/{user}/role', [ProjectMemberController::class, 'changeRole'])->name('projects.members.changeRole');
        Route::delete('/projects/{project}/members/me', [ProjectMemberController::class, 'leave'])->name('projects.members.leave');

        // Project invitation management routes
        Route::get('/projects/{project}/invitations', [ProjectInvitationController::class, 'index'])->name('projects.invitations.index');
        Route::post('/projects/{project}/invitations', [ProjectInvitationController::class, 'store'])->name('projects.invitations.store');
        Route::post('/project-invitations/{token}/accept', [ProjectInvitationController::class, 'accept'])->name('project-invitations.do-accept');
        Route::post('/project-invitations/{invitation}/resend', [ProjectInvitationController::class, 'resend'])->name('projects.invitations.resend');
        Route::delete('/project-invitations/{invitation}', [ProjectInvitationController::class, 'destroy'])->name('projects.invitations.destroy');

        // Project settings routes
        Route::get('/projects/{project}/settings', [ProjectSettingsController::class, 'show'])->name('projects.settings.show');
        Route::put('/projects/{project}/settings/general', [ProjectSettingsController::class, 'updateGeneral'])->name('projects.settings.updateGeneral');
        Route::put('/projects/{project}/settings/privacy', [ProjectSettingsController::class, 'updatePrivacy'])->name('projects.settings.updatePrivacy');
        Route::post('/projects/{project}/archive', [ProjectSettingsController::class, 'archive'])->name('projects.archive');
        Route::post('/projects/{project}/unarchive', [ProjectSettingsController::class, 'unarchive'])->name('projects.unarchive');
        Route::post('/projects/{project}/delete', [ProjectSettingsController::class, 'delete'])->name('projects.delete');
        Route::post('/projects/{project}/duplicate', [ProjectSettingsController::class, 'duplicate'])->name('projects.duplicate');

        // Project activity routes
        Route::get('/projects/{project}/activity', [ProjectActivityController::class, 'index'])->name('projects.activity.index');

        // Project view routes
        Route::get('/projects/{project}/views/tasks', [ProjectViewController::class, 'getTasks'])->name('projects.views.tasks');
        Route::get('/projects/{project}/views/files', [ProjectViewController::class, 'getFiles'])->name('projects.views.files');
        Route::get('/projects/{project}/views/dashboard', [ProjectViewController::class, 'getDashboardData'])->name('projects.views.dashboard');
        Route::post('/projects/{project}/views/preferences', [ProjectViewController::class, 'saveViewPreferences'])->name('projects.views.preferences.save');
        Route::get('/projects/{project}/views/preferences/{viewType}', [ProjectViewController::class, 'getViewPreferences'])->name('projects.views.preferences.get');
    });

    // Attachment routes
    Route::post('/tasks/{task}/attachments', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::get('/attachments/{attachment}', [AttachmentController::class, 'show'])->name('attachments.show');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // Private Notes routes
    Route::get('/api/private-note', [PrivateNoteController::class, 'show'])->name('private-note.show');
    Route::put('/api/private-note', [PrivateNoteController::class, 'update'])->name('private-note.update');
    Route::delete('/api/private-note', [PrivateNoteController::class, 'destroy'])->name('private-note.destroy');
});


// Admin routes (super admin only)
Route::prefix('admin')
    ->middleware(['auth', 'password.set', 'super.admin'])
    ->name('admin.')
    ->group(function () {
        // Dashboard
        Route::get('/', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');

        // Users management
        Route::get('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/impersonate', [\App\Http\Controllers\Admin\AdminUserController::class, 'impersonate'])->name('users.impersonate');
        Route::get('/stop-impersonating', [\App\Http\Controllers\Admin\AdminUserController::class, 'stopImpersonating'])->name('stop-impersonating');
        Route::post('/users/{user}/assign-plan', [\App\Http\Controllers\Admin\AdminUserController::class, 'assignPlan'])->name('users.assign-plan');
        Route::post('/users/{user}/extend-trial', [\App\Http\Controllers\Admin\AdminUserController::class, 'extendTrial'])->name('users.extend-trial');
        Route::patch('/users/{user}/suspend', [\App\Http\Controllers\Admin\AdminUserController::class, 'suspend'])->name('users.suspend');
        Route::patch('/users/{user}/activate', [\App\Http\Controllers\Admin\AdminUserController::class, 'activate'])->name('users.activate');
        Route::delete('/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'destroy'])->name('users.destroy');

        // Workspaces management
        Route::get('/workspaces', [\App\Http\Controllers\Admin\AdminWorkspaceController::class, 'index'])->name('workspaces.index');
        Route::patch('/workspaces/{organization}/deactivate', [\App\Http\Controllers\Admin\AdminWorkspaceController::class, 'deactivate'])->name('workspaces.deactivate');
        Route::patch('/workspaces/{organization}/activate', [\App\Http\Controllers\Admin\AdminWorkspaceController::class, 'activate'])->name('workspaces.activate');
        Route::delete('/workspaces/{organization}', [\App\Http\Controllers\Admin\AdminWorkspaceController::class, 'destroy'])->name('workspaces.destroy');

        // Settings & Billing
        Route::get('/settings', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/google/test', [GoogleSettingsController::class, 'testCredentials'])->name('settings.google.test');

        // Plans management
        Route::get('/settings/plans', [\App\Http\Controllers\Admin\AdminPlanController::class, 'index'])->name('plans.index');
        Route::post('/settings/plans', [\App\Http\Controllers\Admin\AdminPlanController::class, 'store'])->name('plans.store');
        Route::patch('/settings/plans/{plan}', [\App\Http\Controllers\Admin\AdminPlanController::class, 'update'])->name('plans.update');
        Route::delete('/settings/plans/{plan}', [\App\Http\Controllers\Admin\AdminPlanController::class, 'destroy'])->name('plans.destroy');

        // Contacts management
        Route::get('/contacts', [\App\Http\Controllers\Admin\AdminContactController::class, 'index'])->name('contacts.index');
        Route::get('/contacts/{contact}', [\App\Http\Controllers\Admin\AdminContactController::class, 'show'])->name('contacts.show');
        Route::patch('/contacts/{contact}/mark-as-read', [\App\Http\Controllers\Admin\AdminContactController::class, 'markAsRead'])->name('contacts.mark-as-read');
        Route::patch('/contacts/{contact}/mark-as-closed', [\App\Http\Controllers\Admin\AdminContactController::class, 'markAsClosed'])->name('contacts.mark-as-closed');
        Route::delete('/contacts/{contact}', [\App\Http\Controllers\Admin\AdminContactController::class, 'destroy'])->name('contacts.destroy');
    });


Route::get('/test-mail', function () {
    \Mail::raw('Test Email Working!', function ($message) {
        $message->to('pobec11786@nexafilm.com')
                ->subject('Laravel SMTP Test');
    });

    return 'Mail sent!';
});

// Debug routes (local only)
if (app()->isLocal()) {
    Route::get('/run-migration', function () {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate');
            return response()->json([
                'success' => true,
                'message' => 'Migration completed successfully',
                'output' => \Illuminate\Support\Facades\Artisan::output(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    });

    Route::get('/debug/my-tasks', function () {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        // Get raw query
        $query = \App\Models\Task::where('assignee_id', $user->id);
        
        return response()->json([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'query' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'total_tasks' => $query->count(),
            'tasks' => $query->limit(10)->get(['id', 'name', 'assignee_id', 'project_id', 'status', 'created_at']),
        ]);
    });

    Route::get('/debug/my-tasks-api', function () {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $service = new \App\Services\MyTasksService();
        $tasks = $service->getUserTasks($user, [], [], null, 1, 50);
        
        return response()->json([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'api_response' => $tasks,
        ]);
    });
}