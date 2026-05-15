<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSectionRequest;
use App\Http\Requests\UpdateSectionRequest;
use App\Models\Project;
use App\Models\Section;
use App\Services\SectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SectionController extends Controller
{
    protected SectionService $sectionService;

    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }

    /**
     * Get all sections for a project.
     */
    public function index(Project $project): JsonResponse
    {
        $sections = $project->sections()
            ->orderBy('position')
            ->with('tasks')
            ->get();

        return response()->json([
            'data' => $sections,
        ]);
    }

    /**
     * Create a new section.
     */
    public function store(StoreSectionRequest $request, Project $project): JsonResponse
    {
        $validated = $request->validated();

        try {
            $section = $this->sectionService->createSection($project, $validated);

            return response()->json([
                'data' => $section,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Get a specific section.
     */
    public function show(Section $section): JsonResponse
    {
        return response()->json([
            'data' => $section->load('tasks'),
        ]);
    }

    /**
     * Update a section.
     */
    public function update(UpdateSectionRequest $request, Section $section): JsonResponse
    {
        $validated = $request->validated();

        try {
            $updatedSection = $this->sectionService->updateSection($section, $validated);

            return response()->json([
                'data' => $updatedSection,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Delete a section.
     */
    public function destroy(Request $request, Section $section): JsonResponse
    {
        $validated = $request->validate([
            'delete_tasks'      => 'boolean',
            'target_section_id' => 'nullable|exists:sections,id',
        ]);

        $this->sectionService->deleteSection($section, $validated);

        return response()->json(null, 204);
    }

    /**
     * Reorder sections within a project.
     */
    public function reorder(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'section_ids' => 'required|array',
            'section_ids.*' => 'exists:sections,id',
        ]);

        try {
            $this->sectionService->reorderSections($project, $validated['section_ids']);

            return response()->json([
                'message' => 'Sections reordered successfully',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
