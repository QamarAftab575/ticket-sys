<?php

namespace App\Services;

use App\Models\User;
use App\Models\BusinessSetting;
use Laravel\Socialite\Contracts\User as ProviderUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleAuthService
{
    private const CACHE_KEY = 'google_oauth_credentials';
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Check if Google login is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        $credentials = $this->getCredentials();
        return $credentials && ($credentials['enabled'] ?? false);
    }

    /**
     * Get Google OAuth credentials from database with caching
     *
     * @return array|null
     */
    public function getCredentials(): ?array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return BusinessSetting::get('google_oauth');
        });
    }

    /**
     * Authenticate a user by email
     *
     * @param string $email
     * @return User|null
     */
    public function authenticateUser(string $email): ?User
    {
        $user = User::where('email', strtolower($email))->first();

        if (!$user) {
            return null;
        }

        // Check if email is verified
        if (!$user->email_verified_at) {
            return null;
        }

        // Check if account is suspended
        if ($user->is_suspended ?? false) {
            return null;
        }

        return $user;
    }

    /**
     * Handle OAuth callback from Google
     *
     * @param ProviderUser $providerUser
     * @return User|null
     */
    public function handleCallback(ProviderUser $providerUser): ?User
    {
        $email = $providerUser->getEmail();
        return $this->authenticateUser($email);
    }

    /**
     * Log authentication attempt
     *
     * @param string $email
     * @param bool $success
     * @param string|null $reason
     * @return void
     */
    public function logAuthenticationAttempt(string $email, bool $success, ?string $reason = null): void
    {
        $logData = [
            'email' => $email,
            'success' => $success,
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
        ];

        if ($success) {
            Log::channel('google_auth')->info('Google authentication successful', $logData);
        } else {
            Log::channel('google_auth')->warning('Google authentication failed', $logData);
        }
    }
}
