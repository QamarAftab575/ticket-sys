<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteMemberRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'organization_id' => 'required|uuid|exists:organizations,id',
            'email' => 'required|email',
            'role' => 'required|in:member',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'organization_id.required' => 'Organization is required.',
            'organization_id.uuid' => 'Invalid organization ID.',
            'organization_id.exists' => 'Organization not found.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'role.required' => 'Role is required.',
            'role.in' => 'Role must be member.',
        ];
    }
}

