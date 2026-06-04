<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Task;
use App\Services\AttachmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttachmentApiController extends Controller
{
    protected AttachmentService $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    /**
     * Get all attachments for a task
     */
    public function index(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $attachments = $task->attachments()
            ->with('uploader:id,name,email,avatar')
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $attachments->map(fn($attachment) => $this->formatAttachment($attachment));

        return response()->json(['data' => $data]);
    }

    /**
     * Upload a new attachment
     */
    public function store(Request $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240'], // 10MB max
        ]);

        $file = $validated['file'];
        $attachment = $this->attachmentService->uploadAttachment($task, $file, Auth::user());

        return response()->json([
            'message' => 'Attachment uploaded successfully',
            'data' => $this->formatAttachment($attachment),
        ], 201);
    }

    /**
     * Get attachment details
     */
    public function show(Attachment $attachment): JsonResponse
    {
        $this->authorize('view', $attachment->task);

        return response()->json([
            'data' => $this->formatAttachment($attachment->load('uploader:id,name,email,avatar')),
        ]);
    }

    /**
     * Download attachment
     */
    public function download(Attachment $attachment)
    {
        $this->authorize('view', $attachment->task);

        $path = $attachment->path;

        if (!Storage::exists($path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return Storage::download($path, $attachment->filename);
    }

    /**
     * Delete attachment
     */
    public function destroy(Attachment $attachment): JsonResponse
    {
        // Only uploader or task owner can delete
        $task = $attachment->task;
        if ($attachment->uploaded_by !== Auth::id() && $task->creator_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->attachmentService->deleteAttachment($attachment);

        return response()->json([
            'message' => 'Attachment deleted successfully',
        ], 204);
    }

    /**
     * Format attachment for response
     */
    private function formatAttachment(Attachment $attachment): array
    {
        $data = [
            'id' => $attachment->id,
            'task_id' => $attachment->task_id,
            'filename' => $attachment->filename,
            'mime_type' => $attachment->mime_type,
            'size' => $attachment->size,
            'url' => $attachment->url,
            'created_at' => $attachment->created_at->toIso8601String(),
        ];

        if ($attachment->relationLoaded('uploader')) {
            $data['uploader'] = $attachment->uploader ? [
                'id' => $attachment->uploader->id,
                'name' => $attachment->uploader->name,
                'email' => $attachment->uploader->email,
                'avatar' => $attachment->uploader->avatar,
            ] : null;
        }

        return $data;
    }
}
