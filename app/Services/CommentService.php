<?php

namespace App\Services;

use App\Events\CommentCreated as CommentCreatedEvent;
use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class CommentService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Add a comment to a task.
     */
    public function addComment(Task $task, User $user, string $content): Comment
    {
        // Strip HTML tags to check for actual text content
        if (empty(trim(strip_tags($content)))) {
            throw new \InvalidArgumentException('The comment content is required.');
        }

        return DB::transaction(function () use ($task, $user, $content) {
            $comment = Comment::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'content' => $content,
            ]);

            // Trigger mention notifications
            $this->notificationService->notifyMentionsInComment($comment, $user);

            // Broadcast real-time event
            // reverb functionality disabled
            // broadcast(new CommentCreatedEvent($comment->load('task')))->toOthers();

            return $comment;
        });
    }

    /**
     * Update a comment's content.
     */
    public function updateComment(Comment $comment, string $content, User $user): Comment
    {
        // Strip HTML tags to check for actual text content
        if (empty(trim(strip_tags($content)))) {
            throw new \InvalidArgumentException('The comment content is required.');
        }

        return DB::transaction(function () use ($comment, $content, $user) {
            $comment->update([
                'content' => $content,
                'edited_at' => now(),
            ]);

            // Trigger mention notifications for edited comment
            $this->notificationService->notifyMentionsInComment($comment->fresh(), $user);

            return $comment->fresh();
        });
    }

    /**
     * Delete a comment — hard delete, also removes any uploaded file attachments.
     */
    public function deleteComment(Comment $comment, User $user): bool
    {
        if (!$this->canDelete($comment, $user)) {
            throw new AuthorizationException('You cannot delete this comment.');
        }

        return DB::transaction(function () use ($comment) {
            // Hard-delete any file attachments linked to this task that were
            // uploaded around the same time as this comment (within same session).
            // Primary cleanup: parse embedded <img src> from comment content and
            // delete matching attachment records + storage files.
            $this->deleteEmbeddedImages($comment->content);

            // Force hard delete (bypass SoftDeletes)
            return $comment->forceDelete();
        });
    }

    /**
     * Parse HTML content for embedded storage images and delete them.
     */
    private function deleteEmbeddedImages(string $content): void
    {
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/', $content, $matches);
        foreach ($matches[1] as $src) {
            // Only delete files hosted on our own storage
            if (!str_contains($src, '/storage/')) continue;
            $relativePath = preg_replace('#^.*/storage/#', '', $src);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($relativePath);
            // Remove attachment record if exists
            \App\Models\Attachment::where('file_path', 'like', '%' . basename($relativePath))->forceDelete();
        }
    }

    /**
     * Check if a user can edit a comment.
     * Comments can only be edited by the author within 15 minutes of creation.
     */
    public function canEdit(Comment $comment, User $user): bool
    {
        // User must be the comment author
        if ($comment->user_id !== $user->id) {
            return false;
        }

        // Check if within 15 minutes of creation
        $createdAt = Carbon::parse($comment->created_at);
        $now = Carbon::now();
        $minutesSinceCreation = $createdAt->diffInMinutes($now);

        return $minutesSinceCreation <= 15;
    }

    /**
     * Check if a user can delete a comment.
     * Comments can be deleted by the author or project managers.
     */
    public function canDelete(Comment $comment, User $user): bool
    {
        // User is the comment author
        if ($comment->user_id === $user->id) {
            return true;
        }

        // User is a project manager (only for project tasks)
        $task = $comment->task;
        $project = $task->project;

        // Personal tasks (no project): only task owner can delete comments
        if ($project === null) {
            return $task->creator_id === $user->id || $task->assignee_id === $user->id;
        }

        return $project->isManager($user);
    }

    /**
     * Extract mentioned users from comment content.
     * Parses @username patterns and returns array of User models.
     * 
     * @param string $content The comment content to parse
     * @return array Array of User models that were mentioned
     */
    public function extractMentions(string $content): array
    {
        // Match @username patterns (alphanumeric and underscores)
        preg_match_all('/@([a-zA-Z0-9_]+)/', $content, $matches);
        
        if (empty($matches[1])) {
            return [];
        }
        
        // Get unique usernames
        $usernames = array_unique($matches[1]);
        
        // Look up users by name (stored in lowercase)
        $mentionedUsers = User::whereIn('name', array_map('strtolower', $usernames))
            ->get()
            ->all();
        
        return $mentionedUsers;
    }

    /**
     * Add a reaction to a comment.
     */
    public function addReaction(Comment $comment, User $user, string $emoji): void
    {
        DB::transaction(function () use ($comment, $user, $emoji) {
            $comment->reactions()->firstOrCreate([
                'comment_id' => $comment->id,
                'user_id' => $user->id,
                'emoji' => $emoji,
            ]);
        });
    }

    /**
     * Remove a reaction from a comment.
     */
    public function removeReaction(Comment $comment, User $user, string $emoji): void
    {
        DB::transaction(function () use ($comment, $user, $emoji) {
            $comment->reactions()
                ->where('user_id', $user->id)
                ->where('emoji', $emoji)
                ->delete();
        });
    }
}
