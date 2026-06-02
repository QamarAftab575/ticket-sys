<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SectionService
{
    /**
     * Create a new section.
     */
    public function createSection(Project $project, array $data): Section
    {
        // Validate section name
        $this->validateSectionName($data['name'] ?? '');

        return DB::transaction(function () use ($project, $data) {
            // Get the next position for this project (zero-based indexing)
            $maxPosition = $project->sections()->max('position');
            $nextPosition = $maxPosition !== null ? $maxPosition + 1 : 0;

            $section = Section::create([
                'project_id' => $project->id,
                'name' => $data['name'],
                'position' => $data['position'] ?? $nextPosition,
            ]);

            return $section;
        });
    }

    /**
     * Update an existing section.
     */
    public function updateSection(Section $section, array $data): Section
    {
        // Validate section name if provided
        if (isset($data['name'])) {
            $this->validateSectionName($data['name']);
        }

        return DB::transaction(function () use ($section, $data) {
            // Define fields that can be updated
            $updatableFields = ['name', 'position'];

            $updateData = [];
            foreach ($updatableFields as $field) {
                if (array_key_exists($field, $data)) {
                    $updateData[$field] = $data[$field];
                }
            }

            // Update the section
            $section->update($updateData);

            return $section->fresh();
        });
    }

    /**
     * Delete a section.
     *
     * Options:
     *  - delete_tasks: bool   — permanently delete all tasks in the section
     *  - target_section_id: string|null — move tasks to this section before deleting
     *
     * If neither option is set and tasks exist, falls back to "Untitled Section".
     */
    public function deleteSection(Section $section, array $options = []): bool
    {
        return DB::transaction(function () use ($section, $options) {
            $taskCount = $section->tasks()->count();

            if ($taskCount > 0) {
                if (!empty($options['delete_tasks'])) {
                    // Hard-delete all tasks in this section
                    $section->tasks()->delete();
                } else {
                    $targetSectionId = $options['target_section_id'] ?? null;

                    if ($targetSectionId) {
                        $target = Section::where('id', $targetSectionId)
                            ->where('project_id', $section->project_id)
                            ->first();
                    } else {
                        $target = null;
                    }

                    if (!$target) {
                        // Fall back to "Untitled Section"
                        $target = Section::firstOrCreate(
                            ['project_id' => $section->project_id, 'name' => 'Untitled Section'],
                            ['position' => 0]
                        );
                    }

                    $section->tasks()->update(['section_id' => $target->id]);
                }
            }

            return $section->delete();
        });
    }

    /**
     * Reorder sections within a project.
     * 
     * @param Project $project
     * @param array $sectionIds Array of section IDs in the desired order
     * @return void
     */
    public function reorderSections(Project $project, array $sectionIds): void
    {
        DB::transaction(function () use ($project, $sectionIds) {
            // Update position for each section based on array order
            foreach ($sectionIds as $index => $sectionId) {
                Section::where('id', $sectionId)
                    ->where('project_id', $project->id)
                    ->update(['position' => $index]);
            }
        });
    }

    /**
     * Validate section name.
     */
    private function validateSectionName(string $name): void
    {
        if (empty($name)) {
            throw ValidationException::withMessages([
                'name' => ['The section name is required.']
            ]);
        }

        if (strlen($name) > 100) {
            throw ValidationException::withMessages([
                'name' => ['The section name must not exceed 100 characters.']
            ]);
        }
    }
}
