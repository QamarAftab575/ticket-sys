<?php

namespace Installation\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class InstallationService
{
    /**
     * Check system requirements.
     */
    public function checkRequirements(): array
    {
        return [
            'php_version' => [
                'name' => 'PHP Version >= 8.2',
                'status' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'value' => PHP_VERSION,
            ],
            'extensions' => [
                'name' => 'Required PHP Extensions',
                'status' => $this->checkExtensions(),
                'value' => implode(', ', $this->getRequiredExtensions()),
            ],
            'writable_storage' => [
                'name' => 'Writable Storage Directory',
                'status' => is_writable(storage_path()),
                'value' => storage_path(),
            ],
            'writable_bootstrap' => [
                'name' => 'Writable Bootstrap Directory',
                'status' => is_writable(base_path('bootstrap')),
                'value' => base_path('bootstrap'),
            ],
            'writable_env' => [
                'name' => 'Writable .env File',
                'status' => is_writable(base_path('.env')),
                'value' => base_path('.env'),
            ],
        ];
    }

    /**
     * Check if all required extensions are loaded.
     */
    private function checkExtensions(): bool
    {
        $required = $this->getRequiredExtensions();

        foreach ($required as $extension) {
            if (!extension_loaded($extension)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get list of required PHP extensions.
     */
    private function getRequiredExtensions(): array
    {
        return ['mbstring', 'openssl', 'pdo', 'json', 'curl', 'bcmath'];
    }

    /**
     * Test database connection with given credentials.
     */
    public function testDatabaseConnection(array $credentials): bool
    {
        try {
            // Build the connection configuration
            $config = [
                'driver' => 'mysql',
                'host' => $credentials['host'],
                'port' => $credentials['port'],
                'database' => $credentials['database'],
                'username' => $credentials['username'],
                'password' => $credentials['password'],
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ];

            // Add the test connection configuration
            config(['database.connections.test_connection' => $config]);

            // Purge any existing connection to ensure fresh connection
            DB::purge('test_connection');

            // Test the connection by trying to get PDO
            $pdo = DB::connection('test_connection')->getPdo();

            // Test that we can actually query the database
            DB::connection('test_connection')->select('SELECT 1');

            // Purge the test connection after successful test
            DB::purge('test_connection');

            return true;
        } catch (\Exception $e) {
            // Purge the test connection on error
            DB::purge('test_connection');
            throw $e;
        }
    }

    /**
     * Update .env file with new values.
     */
    public function updateEnv(array $values): bool
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            return false;
        }

        $envContent = File::get($envPath);

        foreach ($values as $key => $value) {
            // Escape special characters
            $escapedValue = addcslashes($value, '\\$"');
            
            // Check if value needs quotes (contains spaces, special chars, or is empty after the first char)
            $needsQuotes = $value !== '' && (
                str_contains($value, ' ') || 
                str_contains($value, '#') || 
                str_contains($value, '"') ||
                str_contains($value, '@') ||
                str_contains($value, '!') ||
                str_contains($value, '$') ||
                str_contains($value, '&') ||
                str_contains($value, '*')
            );
            
            if ($needsQuotes && !str_starts_with($escapedValue, '"')) {
                $escapedValue = '"' . $escapedValue . '"';
            }

            if (preg_match("/^" . preg_quote($key) . "=/m", $envContent)) {
                // Key exists, update it
                $envContent = preg_replace(
                    "/^" . preg_quote($key) . "=.*/m",
                    $key . "=" . $escapedValue,
                    $envContent
                );
            } else {
                // Key doesn't exist, append it
                $envContent .= "\n" . $key . "=" . $escapedValue;
            }
        }

        File::put($envPath, $envContent);

        // Give filesystem time to write
        usleep(100000); // 100ms

        return true;
    }

    /**
     * Create the admin user as super admin.
     */
    public function createAdminUser(string $name, string $email, string $password): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
            'is_super_admin' => true,
        ]);
    }

    /**
     * Create the first workspace for the admin user.
     */
    public function createFirstWorkspace(User $user, string $workspaceName): void
    {
        try {
            $organizationService = app(\App\Services\OrganizationService::class);

            // Create the organization/workspace
            $organization = $organizationService->createOrganization([
                'name' => $workspaceName,
                'description' => 'Default workspace created during installation',
                'avatar_color' => '#3B82F6',
            ], $user);

            // Assign user as workspace owner
            $organizationService->addMember($organization, $user, 'workspace_owner');

            // Seed starter project, sections, and tasks
            app(\App\Services\OnboardingDataService::class)->seedWorkspace($organization, $user);
        } catch (\Exception $e) {
            \Log::error('Failed to create first workspace during installation: ' . $e->getMessage());
            // Don't throw exception - installation should still complete
        }
    }

    /**
     * Remove Installation namespace from composer.json.
     *
     * After installation, we remove the Installation namespace from
     * composer.json autoload to ensure no issues if the folder is deleted.
     */
    public function removeInstallationFromComposer(): bool
    {
        $composerPath = base_path('composer.json');

        if (!File::exists($composerPath)) {
            return false;
        }

        $composer = json_decode(File::get($composerPath), true);

        // Remove the Installation namespace from autoload
        if (isset($composer['autoload']['psr-4']['Installation\\'])) {
            unset($composer['autoload']['psr-4']['Installation\\']);
        }

        // Write back to composer.json
        File::put($composerPath, json_encode($composer, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL);

        return true;
    }

    /**
     * Drop all tables in the database (for clean retry after failed installation).
     */
    public function dropAllTables(): bool
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            $tables = DB::select('SHOW TABLES');
            $databaseName = config('database.connections.mysql.database');
            
            foreach ($tables as $table) {
                $tableName = $table->{"Tables_in_{$databaseName}"};
                DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
            }
            
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to drop all tables: ' . $e->getMessage());
            return false;
        }
    }
}
