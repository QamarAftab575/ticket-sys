<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AttachmentService
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Allowed file types and their extensions.
     */
    private const ALLOWED_TYPES = [
        'images'       => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'pdfs'         => ['pdf'],
        'documents'    => ['doc', 'docx', 'txt', 'csv'],
        'spreadsheets' => ['xls', 'xlsx'],
        'archives'     => ['zip', 'tar', 'gz'],
        'videos'       => ['mp4', 'mov', 'avi', 'webm'],
    ];

    private const MAX_FILE_SIZE = 104857600; // 100 MB

    /**
     * Upload a file attachment to a task.
     */
    public function uploadFile(Task $task, User $user, UploadedFile $file, ?string $commentId = null): Attachment
    {
        // Validate file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw ValidationException::withMessages([
                'file' => ['The file size must not exceed 10 MB.']
            ]);
        }

        // Validate file type
        $extension = strtolower($file->getClientOriginalExtension());
        if (!$this->isAllowedFileType($extension)) {
            throw ValidationException::withMessages([
                'file' => ['The file type is not allowed. Allowed types: images (jpg, jpeg, png, gif), PDFs, documents (doc, docx), spreadsheets (xls, xlsx), archives (zip, tar, gz).']
            ]);
        }

        return DB::transaction(function () use ($task, $user, $file, $extension, $commentId) {
            // Generate unique filename
            $filename = $file->getClientOriginalName();
            $uniqueFilename = time() . '_' . uniqid() . '.' . $extension;

            // Store file in attachments directory
            $filePath = $file->storeAs('attachments', $uniqueFilename, 'public');

            // Create attachment record
            $attachment = Attachment::create([
                'task_id'    => $task->id,
                'comment_id' => $commentId,
                'user_id'    => $user->id,
                'type'       => 'file',
                'filename'   => $filename,
                'file_path'  => $filePath,
                'file_size'  => $file->getSize(),
                'mime_type'  => $file->getMimeType(),
                'url'        => Storage::url($filePath),
            ]);

            // Generate thumbnail for images
            if ($this->isImageFile($extension)) {
                $this->generateThumbnail($attachment);
            }

            return $attachment;
        });
    }

    /**
     * Add a link attachment to a task.
     */
    public function addLink(Task $task, User $user, string $url, ?string $title = null): Attachment
    {
        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw ValidationException::withMessages([
                'url' => ['The URL format is invalid.']
            ]);
        }

        return DB::transaction(function () use ($task, $user, $url, $title) {
            // Create link attachment record
            $attachment = Attachment::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'type' => 'link',
                'url' => $url,
                'title' => $title,
                'filename' => null,
                'file_path' => null,
                'file_size' => null,
            ]);

            // Log the attachment addition
            $this->activityLogService->logTaskUpdated(
                $task,
                $user,
                'attachment',
                null,
                'Link added: ' . ($title ?? $url)
            );

            return $attachment;
        });
    }

    /**
     * Generate a thumbnail for an image attachment.
     * Requires intervention/image — skipped gracefully if not available.
     */
    public function generateThumbnail(Attachment $attachment): ?string
    {
        if ($attachment->type !== 'file' || !$attachment->file_path) {
            return null;
        }

        $extension = pathinfo($attachment->filename, PATHINFO_EXTENSION);
        if (!$this->isImageFile($extension)) {
            return null;
        }

        // Skip silently if Intervention Image is not installed
        if (!class_exists(\Intervention\Image\Facades\Image::class)) {
            return null;
        }

        try {
            $originalPath   = storage_path('app/public/' . $attachment->file_path);
            $thumbnailFilename = 'thumb_' . basename($attachment->file_path);
            $thumbnailPath  = 'thumbnails/' . $thumbnailFilename;
            $thumbnailFullPath = storage_path('app/public/' . $thumbnailPath);

            $thumbnailDir = dirname($thumbnailFullPath);
            if (!file_exists($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }

            \Intervention\Image\Facades\Image::make($originalPath)
                ->fit(150, 150)
                ->save($thumbnailFullPath);

            return Storage::url($thumbnailPath);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if the file extension is allowed.
     */
    private function isAllowedFileType(string $extension): bool
    {
        foreach (self::ALLOWED_TYPES as $types) {
            if (in_array($extension, $types)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the file extension is an image type.
     */
    private function isImageFile(string $extension): bool
    {
        return in_array(strtolower($extension), self::ALLOWED_TYPES['images']);
    }

    /**
     * Delete an attachment from a task.
     */
    public function deleteAttachment(Attachment $attachment, User $user): bool
    {
        return DB::transaction(function () use ($attachment, $user) {
            $task = $attachment->task;
            
            // Delete physical file from storage if it's a file attachment
            if ($attachment->type === 'file' && $attachment->file_path) {
                // Delete the main file
                if (Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
                
                // Delete thumbnail if it exists
                $thumbnailPath = 'thumbnails/thumb_' . basename($attachment->file_path);
                if (Storage::disk('public')->exists($thumbnailPath)) {
                    Storage::disk('public')->delete($thumbnailPath);
                }
            }

            // Log the deletion
            $logValue = $attachment->type === 'file' 
                ? 'File removed: ' . $attachment->filename
                : 'Link removed: ' . ($attachment->title ?? $attachment->url);
            
            $this->activityLogService->logTaskUpdated(
                $task,
                $user,
                'attachment',
                $logValue,
                null
            );

            // Delete the database record
            return $attachment->delete();
        });
    }
}
