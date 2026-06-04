<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CustomField;
use App\Models\Project;
use App\Services\CustomFieldService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomFieldApiController extends Controller
{
    protected CustomFieldService $customFieldService;

    public function __construct(CustomFieldService $customFieldService)
    {
        $this->customFieldService = $customFieldService;
    }

    /**
     * Get all custom fields for a project
     */
    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $customFields = $project->customFields()->orderBy('order')->get();

        $data = $customFields->map(fn($field) => $this->formatCustomField($field));

        return response()->json(['data' => $data]);
    }

    /**
     * Create a new custom field
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:text,number,date,dropdown,checkbox,url'],
            'description' => ['nullable', 'string'],
            'options' => ['nullable', 'array'],
            'options.*' => ['string'],
            'is_required' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $customField = $this->customFieldService->createCustomField($project, $validated);

        return response()->json([
            'message' => 'Custom field created successfully',
            'data' => $this->formatCustomField($customField),
        ], 201);
    }

    /**
     * Get custom field details
     */
    public function show(CustomField $customField): JsonResponse
    {
        $this->authorize('view', $customField->project);

        return response()->json([
            'data' => $this->formatCustomField($customField),
        ]);
    }

    /**
     * Update custom field
     */
    public function update(Request $request, CustomField $customField): JsonResponse
    {
        $this->authorize('update', $customField->project);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:text,number,date,dropdown,checkbox,url'],
            'description' => ['nullable', 'string'],
            'options' => ['nullable', 'array'],
            'options.*' => ['string'],
            'is_required' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $updatedField = $this->customFieldService->updateCustomField($customField, $validated);

        return response()->json([
            'message' => 'Custom field updated successfully',
            'data' => $this->formatCustomField($updatedField),
        ]);
    }

    /**
     * Delete custom field
     */
    public function destroy(CustomField $customField): JsonResponse
    {
        $this->authorize('update', $customField->project);

        $this->customFieldService->deleteCustomField($customField);

        return response()->json([
            'message' => 'Custom field deleted successfully',
        ], 204);
    }

    /**
     * Toggle custom field active status
     */
    public function toggleActive(CustomField $customField): JsonResponse
    {
        $this->authorize('update', $customField->project);

        $customField->update(['is_active' => !$customField->is_active]);

        return response()->json([
            'message' => 'Custom field status toggled successfully',
            'data' => $this->formatCustomField($customField->fresh()),
        ]);
    }

    /**
     * Format custom field for response
     */
    private function formatCustomField(CustomField $field): array
    {
        return [
            'id' => $field->id,
            'project_id' => $field->project_id,
            'name' => $field->name,
            'type' => $field->type,
            'description' => $field->description,
            'options' => $field->options,
            'is_required' => $field->is_required,
            'is_active' => $field->is_active,
            'order' => $field->order,
            'created_at' => $field->created_at->toIso8601String(),
            'updated_at' => $field->updated_at->toIso8601String(),
        ];
    }
}
