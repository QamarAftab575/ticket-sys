<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization will be checked in the controller
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'organization_id' => 'required|uuid|exists:organizations,id',
            'name'            => 'required|string|max:255',
            'visibility'      => 'required|in:public_to_team,private_to_members',
            'member_ids'      => 'nullable|array',
            'member_ids.*'    => 'uuid|exists:users,id',
        ];
    }
}
