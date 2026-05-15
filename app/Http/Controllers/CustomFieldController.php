<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomFieldRequest;
use App\Http\Requests\UpdateCustomFieldRequest;
use App\Models\CustomField;
use App\Models\Project;
use App\Models\Task;
use App\Services\CustomFieldService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomFieldController extends Controller
{
    public function __construct(protected CustomFieldService $customFieldService) {}

    /**
     * List all custom fields for a project (active + inactive).
     */
    public function index(Project $project): JsonResponse
    {
        $fields = $project->customFields()->get();

        return response()->json(['data' => $fields]);
    }

    /**
     * Create a new custom field for a project.
     */
    public function store(StoreCustomFieldRequest $request, Project $project): JsonResponse
    {
        try {
            $field = ($request->boolean('is_global'))
                ? $this->customFieldService->createGlobalCustomField($request->validated())
                : $this->customFieldService->createCustomField($project, $request->validated());

            return response()->json(['data' => $field], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function show(CustomField $customField): JsonResponse
    {
        return response()->json(['data' => $customField]);
    }

    public function update(UpdateCustomFieldRequest $request, CustomField $customField): JsonResponse
    {
        try {
            $updated = $this->customFieldService->updateCustomField($customField, $request->validated());
            return response()->json(['data' => $updated]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Toggle the is_active flag on a custom field.
     */
    public function toggleActive(CustomField $customField): JsonResponse
    {
        $updated = $this->customFieldService->toggleActive($customField);
        return response()->json(['data' => $updated]);
    }

    public function destroy(CustomField $customField): JsonResponse
    {
        $this->customFieldService->deleteCustomField($customField);
        return response()->json(null, 204);
    }

    /**
     * Set a custom field value on a task.
     */
    public function setValue(Request $request, Task $task, CustomField $customField): JsonResponse
    {
        $request->validate(['value' => 'present']);

        try {
            $cfv = $this->customFieldService->setFieldValue($task, $customField, $request->input('value'));
            return response()->json(['data' => $cfv]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }
}
