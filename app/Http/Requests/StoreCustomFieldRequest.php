<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomFieldRequest extends FormRequest
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
            'name'             => 'required|string|min:1|max:100',
            'field_type'       => 'required|in:text,number,date,single_select,multi_select,people,dropdown,currency,system_assignee,system_blocked_by,system_blocking,system_completed_on,system_last_modified_on,system_created_on,system_created_by,system_collaborators',
            'options'          => 'nullable|array',
            'options.*.id'     => 'nullable|string',
            'options.*.name'   => 'required_with:options|string|max:100',
            'options.*.color'  => 'nullable|string|max:20',
            'options.*.position' => 'nullable|integer',
            'is_global'        => 'nullable|boolean',
            'is_active'        => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Custom field name is required.',
            'name.max' => 'Custom field name must not exceed 100 characters.',
            'field_type.required' => 'Field type is required.',
            'field_type.in' => 'Invalid field type. Must be one of: text, number, date, dropdown, multi_select, currency.',
        ];
    }
}
