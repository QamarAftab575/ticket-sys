<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Task;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    protected CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * Get paginated comments for a task (newest first).
     */
    public function index(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $comments = $task->comments()
            ->with(['user:id,name,email,avatar', 'attachments'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data'      => $comments->items(),
            'has_more'  => $comments->hasMorePages(),
            'next_page' => $comments->hasMorePages() ? $comments->currentPage() + 1 : null,
            'total'     => $comments->total(),
        ]);
    }

    /**
     * Add a comment to a task.
     */
    public function store(StoreCommentRequest $request, Task $task): JsonResponse
    {
        // Authorization check: user must be able to comment on the task
        $this->authorize('comment', $task);

        $comment = $this->commentService->addComment(
            $task,
            auth()->user(),
            $request->input('content')
        );

        return response()->json([
            'data' => [
                'id' => $comment->id,
                'task_id' => $comment->task_id,
                'user_id' => $comment->user_id,
                'content' => $comment->content,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'email' => $comment->user->email,
                    'avatar' => $comment->user->avatar,
                ],
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
            ],
        ], 201);
    }

    /**
     * Update a comment.
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {

      
        // Authorization check: only comment author can edit
        if ($comment->user_id !== auth()->id()) {
            abort(403, 'You can only edit your own comments');
        }

        $updatedComment = $this->commentService->updateComment(
            $comment,
            $request->input('content'),
            auth()->user()
        );

        return response()->json([
            'data' => [
                'id' => $updatedComment->id,
                'task_id' => $updatedComment->task_id,
                'user_id' => $updatedComment->user_id,
                'content' => $updatedComment->content,
                'edited_at' => $updatedComment->edited_at,
                'user' => [
                    'id' => $updatedComment->user->id,
                    'name' => $updatedComment->user->name,
                    'email' => $updatedComment->user->email,
                    'avatar' => $updatedComment->user->avatar,
                ],
                'created_at' => $updatedComment->created_at,
                'updated_at' => $updatedComment->updated_at,
            ],
        ]);
    }

    /**
     * Delete a comment.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        $user = auth()->user();

        try {
            $this->commentService->deleteComment($comment, $user);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
