<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Helpers\EnvHelper;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    /**
     * Display billing, trial, and social login settings.
     */
    public function index()
    {
        return inertia('Admin/Settings/Index', [
            'settings' => [
                'stripe_key' => EnvHelper::getEnvValue('STRIPE_KEY', ''),
                'stripe_secret' => EnvHelper::getEnvValue('STRIPE_SECRET', ''),
                'free_trial_days' => EnvHelper::getEnvValue('FREE_TRIAL_FOR_NEW_USERS', '10'),
                'workspace_for_trial_users' => EnvHelper::getEnvValue('WORKSPACE_FOR_TRIAL_USERS', '1'),
                'project_per_workspace_for_trial_users' => EnvHelper::getEnvValue('PROJECT_PER_WORKSPACE_FOR_TRIAL_USERS', '5'),
                'members_per_project_for_trial_users' => EnvHelper::getEnvValue('MEMBERS_PER_PROJECT_FOR_TRIAL_USERS', '20'),
            ],
            'googleSettings' => BusinessSetting::get('google_oauth'),
            'googleStatus' => $this->getGoogleSettingsStatus(),
            'plans' => \App\Models\Plan::orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Get Google OAuth settings status
     */
    private function getGoogleSettingsStatus(): array
    {
        $settings = BusinessSetting::get('google_oauth');

        return [
            'enabled' => $settings['enabled'] ?? false,
            'configured' => !empty($settings['client_id']) && !empty($settings['client_secret']),
            'last_updated' => BusinessSetting::where('key', 'google_oauth')->first()?->updated_at,
        ];
    }

    /**
     * Update billing, trial, and social login settings.
     */
    public function update(Request $request)
    {
        // Validate stripe and trial settings if present
        if ($request->has('stripe_key') || $request->has('stripe_secret') || $request->has('free_trial_days') || 
            $request->has('workspace_for_trial_users') || $request->has('project_per_workspace_for_trial_users') || 
            $request->has('members_per_project_for_trial_users')) {
            
            $request->validate([
                'stripe_key' => 'nullable|string',
                'stripe_secret' => 'nullable|string',
                'free_trial_days' => 'nullable|integer|min:0|max:999',
                'workspace_for_trial_users' => 'nullable|integer|min:1|max:999',
                'project_per_workspace_for_trial_users' => 'nullable|integer|min:1|max:999',
                'members_per_project_for_trial_users' => 'nullable|integer|min:1|max:999',
            ]);

            // Update env file with new values
            $envUpdates = [];
            
            if ($request->has('stripe_key')) {
                $envUpdates['STRIPE_KEY'] = $request->stripe_key;
            }
            
            if ($request->has('stripe_secret')) {
                $envUpdates['STRIPE_SECRET'] = $request->stripe_secret;
            }
            
            if ($request->has('free_trial_days')) {
                $envUpdates['FREE_TRIAL_FOR_NEW_USERS'] = $request->free_trial_days;
            }

            if ($request->has('workspace_for_trial_users')) {
                $envUpdates['WORKSPACE_FOR_TRIAL_USERS'] = $request->workspace_for_trial_users;
            }

            if ($request->has('project_per_workspace_for_trial_users')) {
                $envUpdates['PROJECT_PER_WORKSPACE_FOR_TRIAL_USERS'] = $request->project_per_workspace_for_trial_users;
            }

            if ($request->has('members_per_project_for_trial_users')) {
                $envUpdates['MEMBERS_PER_PROJECT_FOR_TRIAL_USERS'] = $request->members_per_project_for_trial_users;
            }

            if (!empty($envUpdates)) {
                EnvHelper::updateMultipleEnvKeys($envUpdates);
            }
        }

        // Validate and save Google OAuth settings if present
        if ($request->has('google_oauth')) {
            $callbackUrl = url('/auth/google/callback');
            
            $request->validate([
                'google_oauth.enabled' => 'boolean',
                'google_oauth.client_id' => 'required_if:google_oauth.enabled,true|min:20',
                'google_oauth.client_secret' => 'required_if:google_oauth.enabled,true|min:20',
                'google_oauth.redirect_uri' => "required_if:google_oauth.enabled,true|url|in:{$callbackUrl}",
            ], [
                'google_oauth.client_id.required_if' => 'Client ID is required when Google login is enabled',
                'google_oauth.client_id.min' => 'Client ID must be at least 20 characters',
                'google_oauth.client_secret.required_if' => 'Client Secret is required when Google login is enabled',
                'google_oauth.client_secret.min' => 'Client Secret must be at least 20 characters',
                'google_oauth.redirect_uri.required_if' => 'Redirect URI is required when Google login is enabled',
                'google_oauth.redirect_uri.url' => 'Redirect URI must be a valid URL',
                'google_oauth.redirect_uri.in' => "Redirect URI must be exactly: {$callbackUrl}",
            ]);

            $credentials = [
                'enabled' => $request->boolean('google_oauth.enabled'),
                'client_id' => $request->input('google_oauth.client_id'),
                'client_secret' => $request->input('google_oauth.client_secret'),
                'redirect_uri' => $request->input('google_oauth.redirect_uri'),
            ];

            BusinessSetting::set('google_oauth', $credentials);
            \Illuminate\Support\Facades\Cache::forget('google_oauth_credentials');
        }

        return back()->with('success', 'Settings saved.');
    }
}
