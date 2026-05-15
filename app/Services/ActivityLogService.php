<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ActivityLogService
{
    /**
     * Log task creation event.
     */
    public function logTaskCreated(Task $task, User $user): void
    {
        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'activity_type' => 'created',
            'field_name' => null,
            'old_value' => null,
            'new_value' => null,
        ]);
    }

    /**
     * Log task field update with old and new values.
     */
    public function logTaskUpdated(Task $task, User $user, string $field, $oldValue, $newValue): void
    {
        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'activity_type' => 'updated',
            'field_name' => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);
    }

    /**
     * Log task completion event.
     */
    public function logTaskCompleted(Task $task, User $user): void
    {
        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'activity_type' => 'completed',
            'field_name' => null,
            'old_value' => null,
            'new_value' => null,
        ]);
    }

    /**
     * Log task deletion event.
     */
    public function logTaskDeleted(Task $task, User $user): void
    {
        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'activity_type' => 'deleted',
            'field_name' => null,
            'old_value' => null,
            'new_value' => null,
        ]);
    }

    /**
     * Log comment creation event.
     */
    public function logComment(Comment $comment): void
    {
        TaskActivity::create([
            'task_id' => $comment->task_id,
            'user_id' => $comment->user_id,
            'activity_type' => 'commented',
            'field_name' => null,
            'old_value' => null,
            'new_value' => null,
            'metadata' => [
                'comment_id' => $comment->id,
                'content' => $comment->content,
            ],
        ]);
    }

    /**
     * Get unified activity feed combining task activities and comments in chronological order.
     */
    public function getActivityFeed(Task $task): Collection
    {
        // Get all task activities
        $activities = TaskActivity::where('task_id', $task->id)
            ->with('user')
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'type' => 'activity',
                    'activity_type' => $activity->activity_type,
                    'user' => $activity->user,
                    'field_name' => $activity->field_name,
                    'old_value' => $activity->old_value,
                    'new_value' => $activity->new_value,
                    'metadata' => $activity->metadata,
                    'created_at' => $activity->created_at,
                ];
            });

        // Get all comments
        $comments = Comment::where('task_id', $task->id)
            ->with('user')
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'type' => 'comment',
                    'user' => $comment->user,
                    'content' => $comment->content,
                    'edited_at' => $comment->edited_at,
                    'created_at' => $comment->created_at,
                ];
            });

        // Merge and sort by created_at in chronological order
        return $activities
            ->merge($comments)
            ->sortBy('created_at')
            ->values();
    }
}
