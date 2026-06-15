<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
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
                'stripe_public_key' => BusinessSetting::get('stripe_public_key'),
                'stripe_secret_key' => BusinessSetting::get('stripe_secret_key'),
                'trial_enabled' => BusinessSetting::get('trial_enabled', '1'),
                'trial_days' => BusinessSetting::get('trial_days', '14'),
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
        if ($request->has('stripe_public_key') || $request->has('stripe_secret_key') || $request->has('trial_enabled')) {
            $request->validate([
                'stripe_public_key' => 'nullable|string',
                'stripe_secret_key' => 'nullable|string',
                'trial_enabled' => 'required|boolean',
                'trial_days' => 'required_if:trial_enabled,true|integer|min:1',
            ]);

            BusinessSetting::set('stripe_public_key', $request->stripe_public_key);
            BusinessSetting::set('stripe_secret_key', $request->stripe_secret_key);
            BusinessSetting::set('trial_enabled', $request->trial_enabled ? '1' : '0');
            if ($request->trial_enabled) {
                BusinessSetting::set('trial_days', $request->trial_days);
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
