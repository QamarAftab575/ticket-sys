<?php

namespace App\Services;

use App\Models\Section;
use App\Models\User;
use Illuminate\Support\Collection;

class MyTasksSectionService
{
    /**
     * Default sections created for every user's My Tasks view.
     * Order matters — it defines the default display order.
     */
    private const DEFAULT_SECTIONS = [
        ['name' => 'Recently Assigned', 'position' => 0],
        ['name' => 'Do Today',          'position' => 1],
        ['name' => 'Do Next Week',      'position' => 2],
        ['name' => 'Do Later',          'position' => 3],
    ];

    /**
     * Get (or create) all My Tasks sections for a user.
     * Ensures the default sections always exist.
     */
    public function getOrCreateSections(User $user): Collection
    {
        $existing = Section::myTasks($user->id)->orderBy('position')->get();

        if ($existing->isEmpty()) {
            $existing = $this->createDefaultSections($user);
        }

        return $existing;
    }

    /**
     * Create the default My Tasks sections for a user.
     */
    public function createDefaultSections(User $user): Collection
    {
        $sections = collect();

        foreach (self::DEFAULT_SECTIONS as $def) {
            $sections->push(Section::create([
                'user_id'     => $user->id,
                'project_id'  => null,
                'name'        => $def['name'],
                'position'    => $def['position'],
                'is_my_tasks' => true,
            ]));
        }

        return $sections;
    }

    /**
     * Create a new custom section for the user's My Tasks.
     */
    public function createSection(User $user, string $name): Section
    {
        $maxPosition = Section::myTasks($user->id)->max('position') ?? -1;

        return Section::create([
            'user_id'     => $user->id,
            'project_id'  => null,
            'name'        => $name,
            'position'    => $maxPosition + 1,
            'is_my_tasks' => true,
        ]);
    }

    /**
     * Rename a My Tasks section (only if it belongs to the user).
     */
    public function renameSection(User $user, Section $section, string $name): Section
    {
        $this->authorizeSection($user, $section);
        $section->update(['name' => $name]);
        return $section->fresh();
    }

    /**
     * Delete a My Tasks section (only if it belongs to the user).
     * Tasks in the section are moved to the first remaining section.
     */
    public function deleteSection(User $user, Section $section): void
    {
        $this->authorizeSection($user, $section);

        // Move tasks to the first other section
        $fallback = Section::myTasks($user->id)
            ->where('id', '!=', $section->id)
            ->orderBy('position')
            ->first();

        if ($fallback) {
            $section->tasks()->update(['section_id' => $fallback->id]);
        }

        $section->delete();

        // Re-number positions
        Section::myTasks($user->id)
            ->orderBy('position')
            ->get()
            ->each(function ($s, $index) {
                $s->update(['position' => $index]);
            });
    }

    /**
     * Reorder My Tasks sections for a user.
     *
     * @param string[] $orderedIds
     */
    public function reorderSections(User $user, array $orderedIds): Collection
    {
        foreach ($orderedIds as $position => $id) {
            Section::myTasks($user->id)
                ->where('id', $id)
                ->update(['position' => $position]);
        }

        return Section::myTasks($user->id)->orderBy('position')->get();
    }

    /**
     * Ensure the given section belongs to the user.
     */
    private function authorizeSection(User $user, Section $section): void
    {
        if (!$section->is_my_tasks || $section->user_id !== $user->id) {
            abort(403, 'This section does not belong to you.');
        }
    }
}
