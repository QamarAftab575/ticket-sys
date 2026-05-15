<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TaskTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TaskTemplateController extends Controller
{
    /**
     * Get all task templates for a project.
     */
    public function index(Project $project): JsonResponse
    {
        $templates = $project->taskTemplates()->get();

        return response()->json([
            'data' => $templates,
        ]);
    }

    /**
     * Create a new task template.
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:1|max:255',
            'template_data' => 'required|array',
        ]);

        try {
            $template = TaskTemplate::create([
                'project_id' => $project->id,
                'name' => $validated['name'],
                'template_data' => $validated['template_data'],
            ]);

            return response()->json([
                'data' => $template,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Get a specific task template.
     */
    public function show(TaskTemplate $template): JsonResponse
    {
        return response()->json([
            'data' => $template,
        ]);
    }

    /**
     * Update a task template.
     */
    public function update(Request $request, TaskTemplate $template): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|min:1|max:255',
            'template_data' => 'sometimes|array',
        ]);

        try {
            $template->update($validated);

            return response()->json([
                'data' => $template->fresh(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Delete a task template.
     */
    public function destroy(TaskTemplate $template): JsonResponse
    {
        $template->delete();

        return response()->json(null, 204);
    }
}
