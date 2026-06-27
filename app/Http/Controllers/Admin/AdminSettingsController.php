<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Helpers\EnvHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Services\MailTestService;

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
            'mailSettings' => [
                'mail_mailer' => EnvHelper::getEnvValue('MAIL_MAILER', 'smtp'),
                'mail_host' => EnvHelper::getEnvValue('MAIL_HOST', '127.0.0.1'),
                'mail_port' => EnvHelper::getEnvValue('MAIL_PORT', '2525'),
                'mail_username' => EnvHelper::getEnvValue('MAIL_USERNAME', ''),
                'mail_password' => EnvHelper::getEnvValue('MAIL_PASSWORD', ''),
                'mail_encryption' => EnvHelper::getEnvValue('MAIL_ENCRYPTION', 'tls'),
                'mail_from_address' => EnvHelper::getEnvValue('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'mail_from_name' => EnvHelper::getEnvValue('MAIL_FROM_NAME', 'Example'),
            ],
            'queueSettings' => [
                'queue_connection' => EnvHelper::getEnvValue('QUEUE_CONNECTION', 'database'),
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

        // Validate and save Mail Settings if present
        if ($request->has('mail_mailer')) {
            $request->validate([
                'mail_mailer' => 'required|string',
                'mail_host' => 'required|string',
                'mail_port' => 'required|numeric',
                'mail_username' => 'nullable|string|required_if:mail_mailer,smtp',
                'mail_password' => 'nullable|string',
                'mail_encryption' => 'nullable|string|in:none,ssl,tls',
                'mail_from_address' => 'required|email',
                'mail_from_name' => 'required|string',
            ], [
                'mail_username.required_if' => 'Username cannot be empty when SMTP is selected.',
            ]);

            $envUpdates = [
                'MAIL_MAILER' => $request->mail_mailer,
                'MAIL_HOST' => $request->mail_host,
                'MAIL_PORT' => $request->mail_port,
                'MAIL_USERNAME' => $request->mail_username ?? '',
                'MAIL_PASSWORD' => $request->mail_password ?? '',
                'MAIL_ENCRYPTION' => $request->mail_encryption === 'none' ? 'null' : ($request->mail_encryption ?? 'null'),
                'MAIL_FROM_ADDRESS' => $request->mail_from_address,
                'MAIL_FROM_NAME' => $request->mail_from_name,
            ];

            EnvHelper::updateMultipleEnvKeys($envUpdates);

            // Clear config cache to apply new settings
            Artisan::call('optimize:clear');
        }

        // Validate and save Queue Settings if present
        if ($request->has('queue_connection')) {
            $request->validate([
                'queue_connection' => 'required|string|in:database,sync',
            ]);

            EnvHelper::updateEnvFile('QUEUE_CONNECTION', $request->queue_connection);

            // Clear config cache so the new connection takes effect immediately
            Artisan::call('optimize:clear');
        }

        return back()->with('success', 'Settings saved.');
    }

    /**
     * Test the SMTP connection without sending an email.
     */
    public function testMailConnection(Request $request, MailTestService $mailTestService)
    {
        $request->validate([
            'host' => 'required|string',
            'port' => 'required|numeric',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'encryption' => 'nullable|string|in:none,ssl,tls',
        ]);

        $config = $request->only(['host', 'port', 'username', 'password', 'encryption']);
        $result = $mailTestService->testConnection($config);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Send a test email using the provided configuration.
     */
    public function sendTestEmail(Request $request, MailTestService $mailTestService)
    {
        $request->validate([
            'to' => 'required|email',
            'message' => 'nullable|string',
            'config' => 'required|array',
            'config.host' => 'required|string',
            'config.port' => 'required|numeric',
            'config.encryption' => 'nullable|string|in:none,ssl,tls',
        ]);

        $config = $request->input('config');
        $to = $request->input('to');
        $message = $request->input('message');

        $result = $mailTestService->sendTestEmail($config, $to, $message);

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
