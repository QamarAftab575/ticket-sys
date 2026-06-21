<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CustomFieldController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\MyTasksController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskTemplateController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// All API routes require authentication
Route::middleware(['web', 'auth:web'])->group(function () {
    // Global search route
    Route::get('/search', [GlobalSearchController::class, 'search'])->name('search');

    // User routes
    Route::get('/users/search', [UserController::class, 'search'])->name('users.search');
    Route::post('/user/active-workspace', [UserController::class, 'setActiveWorkspace'])->name('user.set-active-workspace');
    // My Tasks routes
    Route::get('/my-tasks', [MyTasksController::class, 'index'])->name('my-tasks.index');

    // Inbox/Notification routes
    Route::prefix('/inbox')->group(function () {
        Route::get('/notifications', [InboxController::class, 'getNotifications'])->name('inbox.notifications');
        Route::get('/unread-count', [InboxController::class, 'getUnreadCount'])->name('inbox.unread-count');
        Route::post('/mark-all-read', [InboxController::class, 'markAllAsRead'])->name('inbox.mark-all-read');
    });

    Route::prefix('/notifications/{notification}')->group(function () {
        Route::post('/mark-read', [InboxController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/mark-unread', [InboxController::class, 'markAsUnread'])->name('notifications.mark-unread');
        Route::delete('/', [InboxController::class, 'destroy'])->name('notifications.destroy');
    });

    // Task routes
    Route::prefix('/projects/{project}')->group(function () {
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    });

    Route::prefix('/tasks/{task}')->group(function () {
        Route::get('/', [TaskController::class, 'show'])->name('tasks.show');
        Route::put('/', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/', [TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::post('/complete', [TaskController::class, 'complete'])->name('tasks.complete');
        Route::post('/reopen', [TaskController::class, 'reopen'])->name('tasks.reopen');
        Route::post('/duplicate', [TaskController::class, 'duplicate'])->name('tasks.duplicate');
        Route::post('/move', [TaskController::class, 'move'])->name('tasks.move');
        Route::get('/activities', [TaskController::class, 'activities'])->name('tasks.activities');
        Route::get('/comments', [CommentController::class, 'index'])->name('comments.index');
        Route::get('/attachments', [AttachmentController::class, 'indexByTask'])->name('attachments.index-by-task');

        // Subtask routes
        Route::get('/subtasks', [TaskController::class, 'subtasks'])->name('tasks.subtasks.index');
        Route::post('/subtasks', [TaskController::class, 'storeSubtask'])->name('tasks.subtasks.store');

        // Dependency routes
        Route::post('/dependencies', [TaskController::class, 'addDependency'])->name('tasks.dependencies.add');
        Route::delete('/dependencies/{dependsOnTask}', [TaskController::class, 'removeDependency'])->name('tasks.dependencies.remove');

        // Comment routes
        Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');

        // Attachment routes
        Route::post('/attachments', [AttachmentController::class, 'store'])->name('attachments.store');

        // Custom field value routes
        Route::post('/custom-fields/{customField}/value', [TaskController::class, 'setCustomFieldValue'])->name('tasks.custom-fields.set-value');
    });

    // Comment routes
    Route::prefix('/comments/{comment}')->group(function () {
        Route::put('/', [CommentController::class, 'update'])->name('comments.update');
        Route::delete('/', [CommentController::class, 'destroy'])->name('comments.destroy');
    });

    // Attachment routes
    Route::prefix('/attachments/{attachment}')->group(function () {
        Route::get('/', [AttachmentController::class, 'show'])->name('attachments.show');
        Route::get('/download', [AttachmentController::class, 'download'])->name('attachments.download');
        Route::delete('/', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
    });

    // Section routes
    Route::prefix('/projects/{project}')->group(function () {
        Route::get('/sections', [SectionController::class, 'index'])->name('sections.index');
        Route::post('/sections', [SectionController::class, 'store'])->name('sections.store');
    });

    Route::prefix('/sections/{section}')->group(function () {
        Route::get('/', [SectionController::class, 'show'])->name('sections.show');
        Route::put('/', [SectionController::class, 'update'])->name('sections.update');
        Route::delete('/', [SectionController::class, 'destroy'])->name('sections.destroy');
    });

    Route::post('/projects/{project}/sections/reorder', [SectionController::class, 'reorder'])->name('sections.reorder');

    // Tag routes
    Route::prefix('/projects/{project}')->group(function () {
        Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
        Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
    });

    Route::prefix('/tags/{tag}')->group(function () {
        Route::get('/', [TagController::class, 'show'])->name('tags.show');
        Route::put('/', [TagController::class, 'update'])->name('tags.update');
        Route::delete('/', [TagController::class, 'destroy'])->name('tags.destroy');
    });

    // Custom Field routes
    Route::prefix('/projects/{project}')->group(function () {
        Route::get('/custom-fields', [CustomFieldController::class, 'index'])->name('custom-fields.index');
        Route::post('/custom-fields', [CustomFieldController::class, 'store'])->name('custom-fields.store');
    });

    Route::prefix('/custom-fields/{customField}')->group(function () {
        Route::get('/', [CustomFieldController::class, 'show'])->name('custom-fields.show');
        Route::put('/', [CustomFieldController::class, 'update'])->name('custom-fields.update');
        Route::delete('/', [CustomFieldController::class, 'destroy'])->name('custom-fields.destroy');
        Route::post('/toggle-active', [CustomFieldController::class, 'toggleActive'])->name('custom-fields.toggle-active');
    });

    // Personal/My Tasks custom fields routes
    Route::prefix('/my-tasks/custom-fields')->group(function () {
        Route::get('/', [CustomFieldController::class, 'indexPersonal'])->name('custom-fields.personal.index');
        Route::post('/', [CustomFieldController::class, 'storePersonal'])->name('custom-fields.personal.store');
    });

    // Project appearance (color + icon) — lightweight PATCH used from project header
    Route::patch('/projects/{project}/appearance', function (\Illuminate\Http\Request $request, \App\Models\Project $project) {
        \Illuminate\Support\Facades\Gate::authorize('update', $project);
        $data = $request->validate([
            'color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon'  => 'nullable|string|max:50',
        ]);
        $project->update($data);
        return response()->json(['color' => $project->color, 'icon' => $project->icon]);
    })->name('projects.appearance');

    // Project members API route for mentions
    Route::get('/projects/{project}/members', [ProjectMemberController::class, 'index'])->name('api.projects.members.index');

    // Task Template routes
    Route::prefix('/projects/{project}')->group(function () {
        Route::get('/task-templates', [TaskTemplateController::class, 'index'])->name('task-templates.index');
        Route::post('/task-templates', [TaskTemplateController::class, 'store'])->name('task-templates.store');
    });

    Route::prefix('/task-templates/{taskTemplate}')->group(function () {
        Route::get('/', [TaskTemplateController::class, 'show'])->name('task-templates.show');
        Route::put('/', [TaskTemplateController::class, 'update'])->name('task-templates.update');
        Route::delete('/', [TaskTemplateController::class, 'destroy'])->name('task-templates.destroy');
    });

    // Subscription status routes
    Route::prefix('/subscription')->group(function () {
        Route::get('/expiry-status', [\App\Http\Controllers\Api\SubscriptionStatusController::class, 'getExpiryStatus'])->name('subscription.expiry-status');
        Route::get('/suspended-workspaces', [\App\Http\Controllers\Api\SubscriptionStatusController::class, 'getSuspendedWorkspaces'])->name('subscription.suspended-workspaces');
        Route::get('/renewal-warning', [\App\Http\Controllers\Api\SubscriptionStatusController::class, 'needsRenewalWarning'])->name('subscription.renewal-warning');
    });
});

// API routes protected by API token authentication (for external apps)
Route::middleware(['api', 'auth.api-token'])->group(function () {
    // Example: Get user's own tasks via API token
    Route::get('/external/tasks', [\App\Http\Controllers\TaskController::class, 'index'])->name('external.tasks.index');
    Route::get('/external/tasks/{task}', [\App\Http\Controllers\TaskController::class, 'show'])->name('external.tasks.show');
    
    // Add more external API endpoints as needed
});
