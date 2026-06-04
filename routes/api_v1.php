<?php

use App\Http\Controllers\Api\V1\AuthApiController;
use App\Http\Controllers\Api\V1\WorkspaceApiController;
use App\Http\Controllers\Api\V1\ProjectApiController;
use App\Http\Controllers\Api\V1\TaskApiController;
use App\Http\Controllers\Api\V1\CustomFieldApiController;
use App\Http\Controllers\Api\V1\CommentApiController;
use App\Http\Controllers\Api\V1\AttachmentApiController;
use App\Http\Controllers\Api\V1\UserApiController;
use App\Http\Controllers\Api\V1\SsoApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Here are the API routes for external applications.
| All routes use API token authentication and return JSON responses.
|
*/

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Registration disabled - users can only join via invitations
    Route::post('/auth/register', [AuthApiController::class, 'register']); // Returns 403 with message
    Route::post('/auth/login', [AuthApiController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthApiController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthApiController::class, 'resetPassword']);
    
    // SSO token exchange (no auth required)
    Route::post('/sso/exchange', [SsoApiController::class, 'exchangeToken']);
    Route::post('/sso/validate', [SsoApiController::class, 'validateToken']);
});

// Protected routes (require API token authentication)
Route::prefix('v1')->middleware(['api', 'auth.api-token'])->group(function () {
    
    // ========================================
    // Authentication & User Management
    // ========================================
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthApiController::class, 'logout']);
        Route::post('/refresh-token', [AuthApiController::class, 'refreshToken']);
        Route::get('/me', [UserApiController::class, 'me']);
    });
    
    // ========================================
    // SSO (Single Sign-On) Routes
    // ========================================
    Route::prefix('sso')->group(function () {
        Route::post('/token', [SsoApiController::class, 'generateToken']);
        Route::post('/token/user/{user}', [SsoApiController::class, 'generateTokenForUser']);
    });

    // ========================================
    // User Routes
    // ========================================
    Route::prefix('users')->group(function () {
        Route::get('/me', [UserApiController::class, 'me']);
        Route::put('/me', [UserApiController::class, 'updateProfile']);
        Route::get('/{user}', [UserApiController::class, 'show']);
        Route::get('/search', [UserApiController::class, 'search']);
    });

    // ========================================
    // Workspace (Organization) Routes
    // ========================================
    Route::prefix('workspaces')->group(function () {
        Route::get('/', [WorkspaceApiController::class, 'index']);
        Route::post('/', [WorkspaceApiController::class, 'store']);
        Route::get('/{organization}', [WorkspaceApiController::class, 'show']);
        Route::put('/{organization}', [WorkspaceApiController::class, 'update']);
        Route::delete('/{organization}', [WorkspaceApiController::class, 'destroy']);
        
        // Workspace members
        Route::get('/{organization}/members', [WorkspaceApiController::class, 'members']);
        Route::post('/{organization}/members/invite', [WorkspaceApiController::class, 'inviteMember']);
        Route::get('/{organization}/members/pending', [WorkspaceApiController::class, 'pendingInvitations']);
        Route::delete('/{organization}/members/{user}', [WorkspaceApiController::class, 'removeMember']);
        Route::patch('/{organization}/members/{user}/role', [WorkspaceApiController::class, 'updateMemberRole']);
        
        // Workspace users
        Route::get('/{organization}/users', [WorkspaceApiController::class, 'users']);
    });

    // ========================================
    // Project Routes
    // ========================================
    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectApiController::class, 'index']);
        Route::post('/', [ProjectApiController::class, 'store']);
        Route::get('/{project}', [ProjectApiController::class, 'show']);
        Route::put('/{project}', [ProjectApiController::class, 'update']);
        Route::delete('/{project}', [ProjectApiController::class, 'destroy']);
        
        // Project actions
        Route::post('/{project}/archive', [ProjectApiController::class, 'archive']);
        Route::post('/{project}/unarchive', [ProjectApiController::class, 'unarchive']);
        Route::post('/{project}/duplicate', [ProjectApiController::class, 'duplicate']);
        
        // Project members
        Route::get('/{project}/members', [ProjectApiController::class, 'members']);
        Route::post('/{project}/members', [ProjectApiController::class, 'addMember']);
        Route::delete('/{project}/members/{user}', [ProjectApiController::class, 'removeMember']);
        Route::patch('/{project}/members/{user}/role', [ProjectApiController::class, 'updateMemberRole']);
        
        // Project users
        Route::get('/{project}/users', [ProjectApiController::class, 'users']);
        
        // Project activity
        Route::get('/{project}/activity', [ProjectApiController::class, 'activity']);
        
        // Project sections
        Route::get('/{project}/sections', [ProjectApiController::class, 'sections']);
        
        // Project tasks
        Route::get('/{project}/tasks', [TaskApiController::class, 'index']);
        Route::post('/{project}/tasks', [TaskApiController::class, 'store']);
        
        // Project custom fields
        Route::get('/{project}/custom-fields', [CustomFieldApiController::class, 'index']);
        Route::post('/{project}/custom-fields', [CustomFieldApiController::class, 'store']);
    });

    // ========================================
    // Task Routes
    // ========================================
    Route::prefix('tasks')->group(function () {
        Route::get('/search', [TaskApiController::class, 'search']);
        Route::get('/{task}', [TaskApiController::class, 'show']);
        Route::put('/{task}', [TaskApiController::class, 'update']);
        Route::delete('/{task}', [TaskApiController::class, 'destroy']);
        
        // Task actions
        Route::post('/{task}/complete', [TaskApiController::class, 'complete']);
        Route::post('/{task}/reopen', [TaskApiController::class, 'reopen']);
        Route::post('/{task}/duplicate', [TaskApiController::class, 'duplicate']);
        Route::post('/{task}/move', [TaskApiController::class, 'move']);
        
        // Task subtasks
        Route::get('/{task}/subtasks', [TaskApiController::class, 'subtasks']);
        Route::post('/{task}/subtasks', [TaskApiController::class, 'createSubtask']);
        
        // Task dependencies
        Route::post('/{task}/dependencies', [TaskApiController::class, 'addDependency']);
        Route::delete('/{task}/dependencies/{dependsOnTask}', [TaskApiController::class, 'removeDependency']);
        
        // Task activities
        Route::get('/{task}/activities', [TaskApiController::class, 'activities']);
        
        // Task comments
        Route::get('/{task}/comments', [CommentApiController::class, 'index']);
        Route::post('/{task}/comments', [CommentApiController::class, 'store']);
        
        // Task attachments
        Route::get('/{task}/attachments', [AttachmentApiController::class, 'index']);
        Route::post('/{task}/attachments', [AttachmentApiController::class, 'store']);
        
        // Task custom field values
        Route::post('/{task}/custom-fields/{customField}/value', [TaskApiController::class, 'setCustomFieldValue']);
    });

    // ========================================
    // Comment Routes
    // ========================================
    Route::prefix('comments')->group(function () {
        Route::get('/{comment}', [CommentApiController::class, 'show']);
        Route::put('/{comment}', [CommentApiController::class, 'update']);
        Route::delete('/{comment}', [CommentApiController::class, 'destroy']);
    });

    // ========================================
    // Attachment Routes
    // ========================================
    Route::prefix('attachments')->group(function () {
        Route::get('/{attachment}', [AttachmentApiController::class, 'show']);
        Route::get('/{attachment}/download', [AttachmentApiController::class, 'download']);
        Route::delete('/{attachment}', [AttachmentApiController::class, 'destroy']);
    });

    // ========================================
    // Custom Field Routes
    // ========================================
    Route::prefix('custom-fields')->group(function () {
        Route::get('/{customField}', [CustomFieldApiController::class, 'show']);
        Route::put('/{customField}', [CustomFieldApiController::class, 'update']);
        Route::delete('/{customField}', [CustomFieldApiController::class, 'destroy']);
        Route::post('/{customField}/toggle-active', [CustomFieldApiController::class, 'toggleActive']);
    });
});
