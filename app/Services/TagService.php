<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class TagService
{
    /**
     * Create a new tag for a project.
     *
     * @param Project $project
     * @param array $data
     * @return Tag
     * @throws ValidationException
     */
    public function createTag(Project $project, array $data): Tag
    {
        $this->validateTagName($data['name'] ?? '');

        return Tag::create([
            'project_id' => $project->id,
            'name' => $data['name'],
            'color' => $data['color'] ?? '#808080',
        ]);
    }

    /**
     * Add a tag to a task.
     *
     * @param Task $task
     * @param Tag $tag
     * @return void
     */
    public function addTagToTask(Task $task, Tag $tag): void
    {
        if (!$task->tags()->where('tag_id', $tag->id)->exists()) {
            $task->tags()->attach($tag->id);
        }
    }

    /**
     * Remove a tag from a task.
     *
     * @param Task $task
     * @param Tag $tag
     * @return void
     */
    public function removeTagFromTask(Task $task, Tag $tag): void
    {
        $task->tags()->detach($tag->id);
    }

    /**
     * Add multiple tags to a task.
     *
     * @param Task $task
     * @param array $tagIds
     * @return void
     */
    public function addTagsToTask(Task $task, array $tagIds): void
    {
        foreach ($tagIds as $tagId) {
            $tag = Tag::find($tagId);
            if ($tag && $tag->project_id === $task->project_id) {
                $this->addTagToTask($task, $tag);
            }
        }
    }

    /**
     * Remove multiple tags from a task.
     *
     * @param Task $task
     * @param array $tagIds
     * @return void
     */
    public function removeTagsFromTask(Task $task, array $tagIds): void
    {
        $task->tags()->detach($tagIds);
    }

    /**
     * Validate tag name length.
     *
     * @param string $name
     * @return void
     * @throws ValidationException
     */
    private function validateTagName(string $name): void
    {
        $length = strlen($name);
        if ($length < 1 || $length > 50) {
            throw ValidationException::withMessages([
                'name' => 'Tag name must be between 1 and 50 characters.',
            ]);
        }
    }

    /**
     * Update a tag.
     *
     * @param Tag $tag
     * @param array $data
     * @return Tag
     * @throws ValidationException
     */
    public function updateTag(Tag $tag, array $data): Tag
    {
        if (isset($data['name'])) {
            $this->validateTagName($data['name']);
        }

        $tag->update([
            'name' => $data['name'] ?? $tag->name,
            'color' => $data['color'] ?? $tag->color,
        ]);

        return $tag;
    }

    /**
     * Delete a tag.
     *
     * @param Tag $tag
     * @return bool
     */
    public function deleteTag(Tag $tag): bool
    {
        return $tag->delete();
    }

    /**
     * Get all tags for a project.
     *
     * @param Project $project
     * @return Collection
     */
    public function getProjectTags(Project $project): Collection
    {
        return $project->tags()->get();
    }
}
