<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoogleSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $callbackUrl = url('/auth/google/callback');
        
        return [
            'enabled' => 'boolean',
            'client_id' => 'required_if:enabled,true|min:20',
            'client_secret' => 'required_if:enabled,true|min:20',
            'redirect_uri' => "required_if:enabled,true|url|in:{$callbackUrl}",
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        $callbackUrl = url('/auth/google/callback');
        
        return [
            'client_id.required_if' => 'Client ID is required when Google login is enabled',
            'client_id.min' => 'Client ID must be at least 20 characters',
            'client_secret.required_if' => 'Client Secret is required when Google login is enabled',
            'client_secret.min' => 'Client Secret must be at least 20 characters',
            'redirect_uri.required_if' => 'Redirect URI is required when Google login is enabled',
            'redirect_uri.url' => 'Redirect URI must be a valid URL',
            'redirect_uri.in' => "Redirect URI must be exactly: {$callbackUrl}",
        ];
    }
}
