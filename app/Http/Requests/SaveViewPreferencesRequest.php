<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveViewPreferencesRequest extends FormRequest
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
            'view_type' => 'required|in:list,board,timeline,calendar,files,dashboard',
            'filters' => 'nullable|json',
            'sort' => 'nullable|json',
            'grouping' => 'nullable|string',
            'column_widths' => 'nullable|json',
            'hidden_columns' => 'nullable|json',
            'collapsed_sections' => 'nullable|json',
            'card_fields' => 'nullable|json',
            'zoom_level' => 'nullable|string',
        ];
    }
}
