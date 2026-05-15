<?php

namespace App\Http\Controllers;

use App\Services\GoogleAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    protected GoogleAuthService $googleAuthService;

    public function __construct(GoogleAuthService $googleAuthService)
    {
        $this->googleAuthService = $googleAuthService;
    }

    /**
     * Redirect to Google OAuth
     *
     * @return RedirectResponse
     */
    public function redirect(): RedirectResponse
    {
        // Check if Google login is enabled
        if (!$this->googleAuthService->isEnabled()) {
            return redirect('/login')->with('error', 'Google login is not enabled. Please contact your administrator.');
        }

        // Get credentials
        $credentials = $this->googleAuthService->getCredentials();
        if (!$credentials || empty($credentials['client_id']) || empty($credentials['client_secret'])) {
            return redirect('/login')->with('error', 'Google login is not properly configured. Please contact your administrator.');
        }

        // Set Google config dynamically from database
        config([
            'services.google.client_id' => $credentials['client_id'],
            'services.google.client_secret' => $credentials['client_secret'],
            'services.google.redirect' => url('/auth/google/callback'),
        ]);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle OAuth callback from Google
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            // Check if Google login is enabled
            if (!$this->googleAuthService->isEnabled()) {
                return redirect('/login')->with('error', 'Google login is not enabled. Please contact your administrator.');
            }

            // Get credentials
            $credentials = $this->googleAuthService->getCredentials();
            if (!$credentials || empty($credentials['client_id']) || empty($credentials['client_secret'])) {
                return redirect('/login')->with('error', 'Google login is not properly configured. Please contact your administrator.');
            }

            // Set Google config dynamically from database
            config([
                'services.google.client_id' => $credentials['client_id'],
                'services.google.client_secret' => $credentials['client_secret'],
                'services.google.redirect' => url('/auth/google/callback'),
            ]);

            // Get provider user from Google
            $providerUser = Socialite::driver('google')->user();

            // Authenticate user
            $user = $this->googleAuthService->handleCallback($providerUser);

            if (!$user) {
                $email = $providerUser->getEmail();
                $this->googleAuthService->logAuthenticationAttempt($email, false, 'User not found or email not verified');

                // Determine error reason
                $existingUser = \App\Models\User::where('email', strtolower($email))->first();
                if (!$existingUser) {
                    return redirect('/login')->with('error', 'No account found with this email. Please register first.');
                } elseif (!$existingUser->email_verified_at) {
                    return redirect('/login')->with('error', 'Please verify your email before logging in with Google.');
                } elseif ($existingUser->is_suspended ?? false) {
                    return redirect('/login')->with('error', 'Your account has been suspended. Please contact support.');
                }

                return redirect('/login')->with('error', 'Authentication failed. Please try again.');
            }

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Log successful authentication
            $this->googleAuthService->logAuthenticationAttempt($user->email, true);

            // Create session
            auth()->login($user, remember: true);

            return redirect('/dashboard')->with('success', 'Successfully logged in with Google');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google OAuth callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect('/login')->with('error', 'Unable to connect to Google. Please try again later.');
        }
    }
}
