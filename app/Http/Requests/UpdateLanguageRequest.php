<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLanguageRequest extends FormRequest
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
        $languageId = $this->route('language');

        return [
            'name' => "required|string|min:1|max:255|unique:languages,name,{$languageId}",
        ];
    }

    /**
     * Get custom messages for validation errors
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Language name is required',
            'name.unique' => 'This language name already exists',
        ];
    }
}
