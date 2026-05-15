<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
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
            'file' => 'nullable|file|max:102400|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,tar,gz,mp4,mov,avi,webm,txt,csv',
            'url' => 'nullable|url',
            'title' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.max'   => 'The file size must not exceed 100 MB.',
            'file.mimes' => 'File type not allowed.',
            'url.url'    => 'The URL format is invalid.',
        ];
    }
}
