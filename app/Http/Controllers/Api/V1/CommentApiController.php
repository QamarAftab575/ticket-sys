<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Task;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentApiController extends Controller
{
    protected CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * Get all comments for a task
     */
    public function index(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $comments = $task->comments()
            ->with('user:id,name,email,avatar')
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $comments->map(fn($comment) => $this->formatComment($comment));

        return response()->json(['data' => $data]);
    }

    /**
     * Create a new comment
     */
    public function store(Request $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $comment = $this->commentService->createComment($task, Auth::user(), $validated['content']);

        return response()->json([
            'message' => 'Comment created successfully',
            'data' => $this->formatComment($comment->load('user:id,name,email,avatar')),
        ], 201);
    }

    /**
     * Get comment details
     */
    public function show(Comment $comment): JsonResponse
    {
        $this->authorize('view', $comment->task);

        return response()->json([
            'data' => $this->formatComment($comment->load('user:id,name,email,avatar')),
        ]);
    }

    /**
     * Update comment
     */
    public function update(Request $request, Comment $comment): JsonResponse
    {
        // Only comment author can update
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $updatedComment = $this->commentService->updateComment($comment, $validated['content']);

        return response()->json([
            'message' => 'Comment updated successfully',
            'data' => $this->formatComment($updatedComment),
        ]);
    }

    /**
     * Delete comment
     */
    public function destroy(Comment $comment): JsonResponse
    {
        // Only comment author or task owner can delete
        $task = $comment->task;
        if ($comment->user_id !== Auth::id() && $task->creator_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->commentService->deleteComment($comment);

        return response()->json([
            'message' => 'Comment deleted successfully',
        ], 204);
    }

    /**
     * Format comment for response
     */
    private function formatComment(Comment $comment): array
    {
        $data = [
            'id' => $comment->id,
            'task_id' => $comment->task_id,
            'content' => $comment->content,
            'created_at' => $comment->created_at->toIso8601String(),
            'updated_at' => $comment->updated_at->toIso8601String(),
        ];

        if ($comment->relationLoaded('user')) {
            $data['user'] = $comment->user ? [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'email' => $comment->user->email,
                'avatar' => $comment->user->avatar,
            ] : null;
        }

        return $data;
    }
}
