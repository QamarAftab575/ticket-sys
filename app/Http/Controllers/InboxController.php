<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InboxController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display the inbox page.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Try to get organization from session first
        $organizationId = session('current_organization_id');
        
        // If no session organization, get user's first active organization
        if (!$organizationId) {
            $organization = $user->organizations()
                ->where('organizations.is_active', true)
                ->whereNull('organization_memberships.deleted_at')
                ->wherePivot('is_active', true)
                ->first();
            
            if (!$organization) {
                // User has no organizations, redirect to onboarding
                return redirect('/onboarding');
            }
            
            $organizationId = $organization->id;
            // Set it in session for future requests
            session(['current_organization_id' => $organizationId]);
        }

        // Get unread count for badge
        $unreadCount = $this->notificationService->getUnreadCount($user, $organizationId);

        return Inertia::render('Inbox/Index', [
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Get paginated notifications for the current user and organization.
     */
    public function getNotifications(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        // Try to get organization from session first
        $organizationId = session('current_organization_id');
        
        // If no session organization, get user's first active organization
        if (!$organizationId) {
            $organization = $user->organizations()
                ->where('organizations.is_active', true)
                ->whereNull('organization_memberships.deleted_at')
                ->wherePivot('is_active', true)
                ->first();
            
            if (!$organization) {
                return response()->json([
                    'data' => [],
                    'has_more' => false,
                    'next_page' => null,
                    'total' => 0,
                    'unread_count' => 0,
                ]);
            }
            
            $organizationId = $organization->id;
        }

        $notifications = Notification::with(['actor:id,name,email,avatar', 'task:id,name,project_id', 'task.project:id,name,color'])
            ->where('user_id', $user->id)
            ->where('organization_id', $organizationId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'data' => $notifications->items(),
            'has_more' => $notifications->hasMorePages(),
            'next_page' => $notifications->hasMorePages() ? $notifications->currentPage() + 1 : null,
            'total' => $notifications->total(),
            'unread_count' => $this->notificationService->getUnreadCount($user, $organizationId),
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        // Authorization: only the notification owner can mark it as read
        if ($notification->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $this->notificationService->markAsRead($notification);

        // Get organization from notification itself
        $organizationId = $notification->organization_id;
        $unreadCount = $this->notificationService->getUnreadCount(auth()->user(), $organizationId);

        return response()->json([
            'message' => 'Notification marked as read',
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a notification as unread.
     */
    public function markAsUnread(Notification $notification): JsonResponse
    {
        // Authorization: only the notification owner can mark it as unread
        if ($notification->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $this->notificationService->markAsUnread($notification);

        // Get organization from notification itself
        $organizationId = $notification->organization_id;
        $unreadCount = $this->notificationService->getUnreadCount(auth()->user(), $organizationId);

        return response()->json([
            'message' => 'Notification marked as unread',
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark all notifications as read for the current user and organization.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        // Try to get organization from session first
        $organizationId = session('current_organization_id');
        
        // If no session organization, get user's first active organization
        if (!$organizationId) {
            $organization = $user->organizations()
                ->where('organizations.is_active', true)
                ->whereNull('organization_memberships.deleted_at')
                ->wherePivot('is_active', true)
                ->first();
            
            if (!$organization) {
                return response()->json(['message' => 'No active organization'], 403);
            }
            
            $organizationId = $organization->id;
        }

        $count = $this->notificationService->markAllAsRead($user, $organizationId);

        return response()->json([
            'message' => "Marked {$count} notifications as read",
            'unread_count' => 0,
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Notification $notification): JsonResponse
    {
        // Authorization: only the notification owner can delete it
        if ($notification->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Get organization before deleting
        $organizationId = $notification->organization_id;
        
        $this->notificationService->deleteNotification($notification);

        $unreadCount = $this->notificationService->getUnreadCount(auth()->user(), $organizationId);

        return response()->json([
            'message' => 'Notification deleted',
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Get unread count for badge.
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        // Try to get organization from session first
        $organizationId = session('current_organization_id');
        
        // If no session organization, get user's first active organization
        if (!$organizationId) {
            $organization = $user->organizations()
                ->where('organizations.is_active', true)
                ->whereNull('organization_memberships.deleted_at')
                ->wherePivot('is_active', true)
                ->first();
            
            if (!$organization) {
                return response()->json(['unread_count' => 0]);
            }
            
            $organizationId = $organization->id;
        }

        $unreadCount = $this->notificationService->getUnreadCount($user, $organizationId);

        return response()->json([
            'unread_count' => $unreadCount,
        ]);
    }
}
