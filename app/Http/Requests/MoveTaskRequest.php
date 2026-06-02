<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoveTaskRequest extends FormRequest
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
            'section_id'           => 'nullable|exists:sections,id',
            'project_id'           => 'nullable|exists:projects,id',
            'position'             => 'nullable|integer|min:0',
            'my_tasks_section_id'  => 'nullable|exists:sections,id',
            'my_tasks_position'    => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'section_id.exists'          => 'The selected section does not exist.',
            'project_id.exists'          => 'The selected project does not exist.',
            'position.integer'           => 'Position must be an integer.',
            'position.min'               => 'Position must be at least 0.',
            'my_tasks_section_id.exists' => 'The selected My Tasks section does not exist.',
            'my_tasks_position.integer'  => 'My Tasks position must be an integer.',
            'my_tasks_position.min'      => 'My Tasks position must be at least 0.',
        ];
    }
}
