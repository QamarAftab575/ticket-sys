<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApiTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();

        // Global admins always have access
        if ($user->hasRole(['super-admin', 'admin'])) {
            return true;
        }

        // Allow if user is owner or admin in any of their workspaces
        return $user->organizations()
            ->wherePivot('is_active', true)
            ->wherePivotIn('role', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'scopes' => ['sometimes', 'array'],
            'scopes.*' => ['string', 'in:read,write,delete'],
            'expires_at' => ['sometimes', 'nullable', 'date', 'after:today'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Token name is required',
            'name.max' => 'Token name must not exceed 255 characters',
            'scopes.array' => 'Scopes must be an array',
            'scopes.*.in' => 'Invalid scope. Allowed: read, write, delete',
            'expires_at.date' => 'Expiration date must be a valid date',
            'expires_at.after' => 'Expiration date must be in the future',
        ];
    }
}
