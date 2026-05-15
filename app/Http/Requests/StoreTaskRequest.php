<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled in the controller
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:1|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'section_id' => 'nullable|exists:sections,id',
            'parent_task_id' => 'nullable|exists:tasks,id',
            'status' => 'nullable|in:to_do,in_progress,blocked,in_review,complete',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'visibility' => 'nullable|in:everyone,private',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'is_milestone' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'custom_fields' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Task name is required.',
            'name.max' => 'Task name must not exceed 255 characters.',
            'due_date.after_or_equal' => 'Due date must be after or equal to start date.',
            'status.in' => 'Invalid status. Must be one of: to_do, in_progress, blocked, in_review, complete.',
            'priority.in' => 'Invalid priority. Must be one of: low, medium, high, urgent.',
            'visibility.in' => 'Invalid visibility. Must be one of: everyone, private.',
        ];
    }
}
