<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectGeneralRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|regex:/^#[0-9A-F]{6}$/i',
            'icon' => 'nullable|string|max:50',
            'status' => 'sometimes|in:on_track,at_risk,off_track,on_hold,complete,archived',
            'owner_id' => 'sometimes|uuid|exists:users,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'color.regex' => 'The color must be a valid hex color code (e.g., #FF5733).',
            'owner_id.exists' => 'The selected owner does not exist.',
        ];
    }
}
