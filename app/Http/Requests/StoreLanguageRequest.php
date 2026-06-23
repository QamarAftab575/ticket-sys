<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLanguageRequest extends FormRequest
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
            'name' => 'required|string|min:1|max:255|unique:languages,name',
            'code' => 'required|string|min:2|max:10|unique:languages,code|regex:/^[a-z]{2}(_[A-Z]{2})?$/',
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
            'code.required' => 'Language code is required',
            'code.unique' => 'This language code already exists',
            'code.regex' => 'Language code must be in format: en, fr, de, ru, etc.',
        ];
    }
}
