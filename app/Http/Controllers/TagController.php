<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TagController extends Controller
{
    protected TagService $tagService;

    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;
    }

    /**
     * Get all tags for a project.
     */
    public function index(Project $project): JsonResponse
    {
        $tags = $this->tagService->getProjectTags($project);

        return response()->json([
            'data' => $tags,
        ]);
    }

    /**
     * Create a new tag.
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:1|max:50',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        try {
            $tag = $this->tagService->createTag($project, $validated);

            return response()->json([
                'data' => $tag,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Get a specific tag.
     */
    public function show(Tag $tag): JsonResponse
    {
        return response()->json([
            'data' => $tag,
        ]);
    }

    /**
     * Update a tag.
     */
    public function update(Request $request, Tag $tag): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|min:1|max:50',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        try {
            $updatedTag = $this->tagService->updateTag($tag, $validated);

            return response()->json([
                'data' => $updatedTag,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Delete a tag.
     */
    public function destroy(Tag $tag): JsonResponse
    {
        $this->tagService->deleteTag($tag);

        return response()->json(null, 204);
    }

    /**
     * Add a tag to a task.
     */
    public function addToTask(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'tag_id' => 'required|exists:tags,id',
        ]);

        $tag = Tag::find($validated['tag_id']);

        if ($tag->project_id !== $task->project_id) {
            return response()->json([
                'error' => 'Tag does not belong to the task\'s project.',
            ], 422);
        }

        $this->tagService->addTagToTask($task, $tag);

        return response()->json([
            'data' => $task->tags()->get(),
        ]);
    }

    /**
     * Remove a tag from a task.
     */
    public function removeFromTask(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'tag_id' => 'required|exists:tags,id',
        ]);

        $tag = Tag::find($validated['tag_id']);

        $this->tagService->removeTagFromTask($task, $tag);

        return response()->json([
            'data' => $task->tags()->get(),
        ]);
    }

    /**
     * Add multiple tags to a task.
     */
    public function addMultipleToTask(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $this->tagService->addTagsToTask($task, $validated['tag_ids']);

        return response()->json([
            'data' => $task->tags()->get(),
        ]);
    }

    /**
     * Remove multiple tags from a task.
     */
    public function removeMultipleFromTask(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $this->tagService->removeTagsFromTask($task, $validated['tag_ids']);

        return response()->json([
            'data' => $task->tags()->get(),
        ]);
    }
}
