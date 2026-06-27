<?php

namespace App\Services;

use App\Events\NotificationCreated as NotificationCreatedEvent;
use App\Enums\NotificationType;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Extract mentioned user IDs from HTML content.
     * Looks for <span data-type="mention" data-id="user-id">
     */
    private function extractMentionedUserIds(string $content): array
    {
        $mentionedIds = [];
        
        // Match mention spans with data-id attribute
        preg_match_all('/<span[^>]*data-type="mention"[^>]*data-id="([^"]+)"[^>]*>/i', $content, $matches);
        
        if (!empty($matches[1])) {
            $mentionedIds = array_unique($matches[1]);
        }
        
        return $mentionedIds;
    }

    /**
     * Create notifications for users mentioned in task description.
     */
    public function notifyMentionsInDescription(Task $task, User $actor): void
    {
        if (empty($task->description)) {
            return;
        }

        $mentionedUserIds = $this->extractMentionedUserIds($task->description);
        
        if (empty($mentionedUserIds)) {
            return;
        }

        foreach ($mentionedUserIds as $userId) {
            // Don't notify the actor themselves
            if ($userId === $actor->id) {
                continue;
            }

            // Check if notification already exists to avoid duplicates
            $exists = Notification::where('user_id', $userId)
                ->where('task_id', $task->id)
                ->where('type', NotificationType::MENTION_IN_DESCRIPTION->value)
                ->where('actor_user_id', $actor->id)
                ->where('created_at', '>=', now()->subMinutes(5)) // Within last 5 minutes
                ->exists();

            if ($exists) {
                continue;
            }

            // Skip if task has no project (personal task)
            if ($task->project === null) {
                continue;
            }

                try {
                $notification = Notification::create([
                    'organization_id' => $task->project->organization_id,
                    'user_id' => $userId,
                    'actor_user_id' => $actor->id,
                    'type' => NotificationType::MENTION_IN_DESCRIPTION->value,
                    'title' => $actor->name . ' mentioned you in ' . $task->name,
                    'message' => 'You were mentioned in the task description',
                    'task_id' => $task->id,
                    'comment_id' => null,
                    'metadata' => [
                        'project_id' => $task->project_id,
                        'project_name' => $task->project->name,
                        'task_name' => $task->name,
                    ],
                ]);
                $this->broadcastNotification($notification);
            } catch (\Exception $e) {
                Log::error('Failed to create mention notification', [
                    'user_id' => $userId,
                    'task_id' => $task->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create notifications for users mentioned in a comment.
     */
    public function notifyMentionsInComment(Comment $comment, User $actor): void
    {
        if (empty($comment->content)) {
            return;
        }

        $mentionedUserIds = $this->extractMentionedUserIds($comment->content);
        
        if (empty($mentionedUserIds)) {
            return;
        }

        $task = $comment->task;

        foreach ($mentionedUserIds as $userId) {
            // Don't notify the actor themselves
            if ($userId === $actor->id) {
                continue;
            }

            // Check if notification already exists to avoid duplicates
            $exists = Notification::where('user_id', $userId)
                ->where('comment_id', $comment->id)
                ->where('type', NotificationType::MENTION_IN_COMMENT->value)
                ->exists();

            if ($exists) {
                continue;
            }

            // Skip if task has no project (personal task)
            if ($task->project === null) {
                continue;
            }

            try {
                $notification = Notification::create([
                    'organization_id' => $task->project->organization_id,
                    'user_id' => $userId,
                    'actor_user_id' => $actor->id,
                    'type' => NotificationType::MENTION_IN_COMMENT->value,
                    'title' => $actor->name . ' mentioned you in a comment',
                    'message' => strip_tags($comment->content),
                    'task_id' => $task->id,
                    'comment_id' => $comment->id,
                    'metadata' => [
                        'project_id' => $task->project_id,
                        'project_name' => $task->project->name,
                        'task_name' => $task->name,
                    ],
                ]);
                $this->broadcastNotification($notification);
            } catch (\Exception $e) {
                Log::error('Failed to create comment mention notification', [
                    'user_id' => $userId,
                    'comment_id' => $comment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Create notification for task assignment.
     */
    public function notifyTaskAssignment(Task $task, User $assignee, User $actor): void
    {
        // Don't notify if assigning to themselves
        if ($assignee->id === $actor->id) {
            return;
        }

        // Check if notification already exists to avoid duplicates
        $exists = Notification::where('user_id', $assignee->id)
            ->where('task_id', $task->id)
            ->where('type', NotificationType::TASK_ASSIGNED->value)
            ->where('actor_user_id', $actor->id)
            ->where('created_at', '>=', now()->subMinutes(5)) // Within last 5 minutes
            ->exists();

        if ($exists) {
            return;
        }

        // Skip if task has no project (personal task)
        if ($task->project === null) {
            return;
        }

        try {
            $notification = Notification::create([
                'organization_id' => $task->project->organization_id,
                'user_id' => $assignee->id,
                'actor_user_id' => $actor->id,
                'type' => NotificationType::TASK_ASSIGNED->value,
                'title' => $actor->name . ' assigned you to ' . $task->name,
                'message' => 'You have been assigned to this task',
                'task_id' => $task->id,
                'comment_id' => null,
                'metadata' => [
                    'project_id' => $task->project_id,
                    'project_name' => $task->project->name,
                    'task_name' => $task->name,
                ],
            ]);
            $this->broadcastNotification($notification);
        } catch (\Exception $e) {
            Log::error('Failed to create task assignment notification', [
                'user_id' => $assignee->id,
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Broadcast a notification to the recipient in real time.
     */
    private function broadcastNotification(Notification $notification): void
    {
        $recipient = $notification->user;

        if (!$recipient) {
            return;
        }

        $unreadCount = $this->getUnreadCount(
            $recipient,
            $notification->organization_id
        );

        // reverb functionality disabled
        // broadcast(new NotificationCreatedEvent($notification, $unreadCount));
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread(Notification $notification): void
    {
        $notification->markAsUnread();
    }

    /**
     * Mark all notifications as read for a user in an organization.
     */
    public function markAllAsRead(User $user, string $organizationId): int
    {
        return Notification::where('user_id', $user->id)
            ->where('organization_id', $organizationId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Get unread count for a user in an organization.
     */
    public function getUnreadCount(User $user, string $organizationId): int
    {
        return Notification::where('user_id', $user->id)
            ->where('organization_id', $organizationId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Delete a notification.
     */
    public function deleteNotification(Notification $notification): void
    {
        $notification->delete();
    }
}
