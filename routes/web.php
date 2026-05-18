<?php

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
use App\Http\Controllers\WorkspaceDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return inertia('Landing');
});

// Authenticated routes
Route::middleware(['auth', 'password.set'])->group(function () {
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
            ->with('members')
            ->limit(6)
            ->get();

        return inertia('Dashboard', [
            'workspaces' => $workspaces,
            'projects' => $projects,
            'userRole' => 'member',
            'workspace' => null,
            'userWorkspaces' => $workspaces,
        ]);
    })->name('dashboard');

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
        $userWorkspaces = $user->organizations()
            ->where('organizations.is_active', true)
            ->wherePivot('is_active', true)
            ->select('organizations.id', 'organizations.name', 'organizations.avatar_color')
            ->get();
        
        return inertia('Settings', [
            'userWorkspaces' => $userWorkspaces,
            'currentWorkspace' => null,
            'userRole' => 'member',
        ]);
    })->name('settings');
    Route::post('/settings/save', [AuthController::class, 'saveSettings'])->name('settings.save');

    // Google settings routes (admin only)
    Route::middleware('role:admin|super-admin')->group(function () {
        Route::get('/settings/integrations/google', [GoogleSettingsController::class, 'show'])->name('settings.google.show');
        Route::post('/settings/integrations/google', [GoogleSettingsController::class, 'update'])->name('settings.google.update');
        Route::post('/settings/integrations/google/test', [GoogleSettingsController::class, 'testCredentials'])->name('settings.google.test');
    });
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
    Route::get('/workspace/{organization}/dashboard', [WorkspaceDashboardController::class, 'show'])->name('workspace.dashboard');
    Route::put('/workspace/{organization}', [WorkspaceDashboardController::class, 'updateWorkspace'])->name('workspace.update');
    Route::post('/workspace/{organization}/switch', [WorkspaceDashboardController::class, 'switchWorkspace'])->name('workspace.switch');
    Route::delete('/workspace/{organization}', [WorkspaceDashboardController::class, 'destroy'])->name('workspace.destroy');

    // Project routes
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

    // Inbox route
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');

    // Project member routes
    Route::get('/projects/{project}/members', [ProjectMemberController::class, 'index'])->name('projects.members.index');
    Route::post('/projects/{project}/members', [ProjectMemberController::class, 'store'])->name('projects.members.store');
    Route::delete('/projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy'])->name('projects.members.destroy');
    Route::patch('/projects/{project}/members/{user}/role', [ProjectMemberController::class, 'changeRole'])->name('projects.members.changeRole');
    Route::delete('/projects/{project}/members/me', [ProjectMemberController::class, 'leave'])->name('projects.members.leave');

    // Project invitation management routes (authenticated)
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

    // My Tasks routes
    Route::get('/my-tasks', [\App\Http\Controllers\MyTasksController::class, 'index'])->name('my-tasks.index');
    Route::get('/my-tasks/api/tasks', [\App\Http\Controllers\MyTasksController::class, 'getTasks'])->name('my-tasks.api.tasks');
    Route::post('/my-tasks/api/preferences', [\App\Http\Controllers\MyTasksController::class, 'saveViewPreferences'])->name('my-tasks.api.preferences.save');
    Route::get('/my-tasks/api/preferences/{viewType}', [\App\Http\Controllers\MyTasksController::class, 'getViewPreferences'])->name('my-tasks.api.preferences.get');

    // Attachment routes
    Route::post('/tasks/{task}/attachments', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::get('/attachments/{attachment}', [AttachmentController::class, 'show'])->name('attachments.show');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
});


Route::get('/test-mail', function () {
    \Mail::raw('Test Email Working!', function ($message) {
        $message->to('pobec11786@nexafilm.com')
                ->subject('Laravel SMTP Test');
    });

    return 'Mail sent!';
});