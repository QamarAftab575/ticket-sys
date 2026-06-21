<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class EnvHelper
{
    /**
     * Update a specific key in the .env file
     *
     * @param string $key The environment variable key
     * @param string $value The environment variable value
     * @return bool Success status
     */
    public static function updateEnvFile(string $key, string $value): bool
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            Log::error("EnvHelper: .env file not found at {$envPath}");
            return false;
        }

        try {
            $envContent = file_get_contents($envPath);

            // Check if key already exists
            if (strpos($envContent, "{$key}=") !== false) {
                // Replace existing key
                $envContent = preg_replace(
                    "/^{$key}=(.*)$/m",
                    "{$key}={$value}",
                    $envContent
                );
            } else {
                // Append new key
                $envContent .= "\n{$key}={$value}\n";
            }

            // Write back to file
            file_put_contents($envPath, $envContent);

            // Clear cached config
            \Illuminate\Support\Facades\Cache::flush();

            Log::info("EnvHelper: Updated {$key} in .env file");

            return true;
        } catch (\Exception $e) {
            Log::error("EnvHelper: Failed to update {$key} in .env file", [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Update multiple environment variables at once
     *
     * @param array $data Key-value pairs to update
     * @return bool Success status
     */
    public static function updateMultipleEnvKeys(array $data): bool
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            Log::error("EnvHelper: .env file not found at {$envPath}");
            return false;
        }

        try {
            $envContent = file_get_contents($envPath);

            foreach ($data as $key => $value) {
                if (strpos($envContent, "{$key}=") !== false) {
                    $envContent = preg_replace(
                        "/^{$key}=(.*)$/m",
                        "{$key}={$value}",
                        $envContent
                    );
                } else {
                    $envContent .= "\n{$key}={$value}";
                }
            }

            file_put_contents($envPath, $envContent);

            // Clear cached config
            \Illuminate\Support\Facades\Cache::flush();

            Log::info("EnvHelper: Updated multiple keys in .env file", [
                'keys' => array_keys($data),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("EnvHelper: Failed to update multiple keys in .env file", [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get value from .env file
     *
     * @param string $key The environment variable key
     * @param string|null $default Default value if not found
     * @return string|null
     */
    public static function getEnvValue(string $key, ?string $default = null): ?string
    {
        return env($key, $default);
    }
}
