<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectPrivacyRequest extends FormRequest
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
            'privacy' => 'required|in:public_to_team,private,specific_members',
            'member_ids' => 'required_if:privacy,specific_members|array',
            'member_ids.*' => 'uuid|exists:users,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'privacy.required' => 'Privacy level is required.',
            'privacy.in' => 'Invalid privacy level selected.',
            'member_ids.required_if' => 'Members must be selected when privacy is set to Specific Members.',
        ];
    }
}
