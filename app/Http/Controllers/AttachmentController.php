<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Attachment;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class AttachmentController extends Controller
{
    /**
     * Get all attachments for a task (excluding comment attachments).
     */
    public function indexByTask(Task $task): JsonResponse
    {
        // Authorization check: user must be able to view the task
        $this->authorize('view', $task);

        $attachments = $task->attachments()
            ->whereNull('comment_id')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'type' => $attachment->type,
                    'filename' => $attachment->filename,
                    'file_size' => $attachment->file_size,
                    'mime_type' => $attachment->mime_type,
                    'url' => $attachment->url,
                    'title' => $attachment->title,
                    'user' => [
                        'id' => $attachment->user->id,
                        'name' => $attachment->user->name,
                        'email' => $attachment->user->email,
                    ],
                    'created_at' => $attachment->created_at,
                    'updated_at' => $attachment->updated_at,
                ];
            });

        return response()->json([
            'data' => $attachments,
        ]);
    }

    /**
     * Upload a file or link attachment to a task.
     */
    public function store(StoreAttachmentRequest $request, Task $task): JsonResponse
    {
        // Authorization check: user must be able to update the task
        $this->authorize('update', $task);

        $service = app(\App\Services\AttachmentService::class);

        // Handle file upload
        if ($request->hasFile('file')) {
            $attachment = $service->uploadFile(
                $task,
                auth()->user(),
                $request->file('file'),
                $request->input('comment_id')
            );
            
            return response()->json([
                'id' => $attachment->id,
                'type' => $attachment->type,
                'filename' => $attachment->filename,
                'file_size' => $attachment->file_size,
                'mime_type' => $attachment->mime_type,
                'url' => $attachment->url,
                'title' => $attachment->title,
                'uploader' => [
                    'id' => $attachment->user->id,
                    'name' => $attachment->user->name,
                    'email' => $attachment->user->email,
                ],
                'uploaded_at' => $attachment->created_at,
                'created_at' => $attachment->created_at,
                'updated_at' => $attachment->updated_at,
            ], 201);
        }

        // Handle link attachment
        if ($request->filled('url')) {
            $attachment = $service->addLink(
                $task,
                auth()->user(),
                $request->input('url'),
                $request->input('title')
            );
            
            return response()->json([
                'id' => $attachment->id,
                'type' => $attachment->type,
                'filename' => $attachment->filename,
                'file_size' => $attachment->file_size,
                'url' => $attachment->url,
                'title' => $attachment->title,
                'uploader' => [
                    'id' => $attachment->user->id,
                    'name' => $attachment->user->name,
                    'email' => $attachment->user->email,
                ],
                'uploaded_at' => $attachment->created_at,
                'created_at' => $attachment->created_at,
                'updated_at' => $attachment->updated_at,
            ], 201);
        }

        return response()->json([
            'message' => 'Either a file or URL must be provided.',
        ], 422);
    }

    /**
     * Download a file attachment.
     */
    public function download(Attachment $attachment): Response
    {
        // Authorization check: user must be able to view the task
        $this->authorize('view', $attachment->task);

        // Only allow downloading file attachments, not links
        if ($attachment->type !== 'file' || !$attachment->file_path) {
            abort(404, 'File not found');
        }

        // Check if file exists in storage
        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File not found');
        }

        // Return file download response
        return response()->download(
            storage_path('app/public/' . $attachment->file_path),
            $attachment->filename
        );
    }

    /**
     * Get attachment metadata.
     */
    public function show(Attachment $attachment): JsonResponse
    {
        // Authorization check: user must be able to view the task
        $this->authorize('view', $attachment->task);

        return response()->json([
            'id' => $attachment->id,
            'type' => $attachment->type,
            'filename' => $attachment->filename,
            'file_size' => $attachment->file_size,
            'url' => $attachment->url,
            'title' => $attachment->title,
            'uploader' => [
                'id' => $attachment->user->id,
                'name' => $attachment->user->name,
                'email' => $attachment->user->email,
            ],
            'uploaded_at' => $attachment->created_at,
            'created_at' => $attachment->created_at,
            'updated_at' => $attachment->updated_at,
        ]);
    }

    /**
     * Delete an attachment.
     */
    public function destroy(Attachment $attachment): JsonResponse
    {
        $task = $attachment->task;

        // Authorization check: user must be able to update the task
        // Allow deletion by uploader or project managers
        if ($attachment->user_id !== auth()->id() && !Gate::allows('update', $task)) {
            abort(403, 'Unauthorized');
        }

        // Use the service to delete the attachment
        $service = app(\App\Services\AttachmentService::class);
        $service->deleteAttachment($attachment, auth()->user());

        return response()->json([
            'message' => 'Attachment deleted successfully',
        ]);
    }
}
