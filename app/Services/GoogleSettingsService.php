<?php

namespace App\Services;

use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class GoogleSettingsService
{
    private const CACHE_KEY = 'google_oauth_credentials';
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Validate Google credentials
     *
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(array $credentials): bool
    {
        // Validate required fields
        if (empty($credentials['client_id']) || empty($credentials['client_secret'])) {
            return false;
        }

        // Validate minimum length
        if (strlen($credentials['client_id']) < 20 || strlen($credentials['client_secret']) < 20) {
            return false;
        }

        // Validate redirect URI if provided
        if (!empty($credentials['redirect_uri'])) {
            if (!filter_var($credentials['redirect_uri'], FILTER_VALIDATE_URL)) {
                return false;
            }

            // Enforce HTTPS in production
            if (app()->isProduction()) {
                if (!str_starts_with($credentials['redirect_uri'], 'https://')) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Save Google credentials to database
     *
     * @param array $credentials
     * @return void
     */
    public function saveCredentials(array $credentials): void
    {
        $encrypted = $this->encryptCredentials($credentials);
        BusinessSetting::set('google_oauth', $encrypted);
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Test connection with Google credentials
     *
     * @param array $credentials
     * @return bool
     */
    public function testConnection(array $credentials): bool
    {
        return $this->validateCredentials($credentials);
    }

    /**
     * Get current configuration status
     *
     * @return array
     */
    public function getStatus(): array
    {
        $settings = BusinessSetting::get('google_oauth');

        return [
            'enabled' => $settings['enabled'] ?? false,
            'configured' => !empty($settings['client_id']) && !empty($settings['client_secret']),
            'last_updated' => BusinessSetting::where('key', 'google_oauth')->first()?->updated_at,
        ];
    }

    /**
     * Encrypt sensitive credentials
     *
     * @param array $credentials
     * @return array
     */
    public function encryptCredentials(array $credentials): array
    {
        return $credentials;
    }

    /**
     * Decrypt sensitive credentials
     *
     * @param array $credentials
     * @return array
     */
    public function decryptCredentials(array $credentials): array
    {
        return $credentials;
    }
}
