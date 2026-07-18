<?php

namespace Installation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Installation\Services\InstallationService;

class InstallController extends Controller
{
    protected InstallationService $installationService;

    public function __construct(InstallationService $installationService)
    {
        $this->installationService = $installationService;
    }

    /**
     * Show the installation welcome page.
     */
    public function index()
    {
        // Force session to start early
        session()->start();
        
        $requirements = $this->installationService->checkRequirements();

        return view('installer::welcome', [
            'appName' => config('app.name'),
            'requirements' => $requirements,
        ]);
    }

    /**
     * Show a specific installation step.
     */
    public function step(string $step)
    {
        // Force session to start early
        session()->start();
        
        $validSteps = ['requirements', 'database', 'site', 'admin', 'email'];

        if (!in_array($step, $validSteps)) {
            return redirect()->route('installer.index');
        }

        // Check if user can access this step (previous steps must be completed)
        if (!$this->canAccessStep($step)) {
            // Redirect to the appropriate step they should be on
            $installerData = session('installer_data', []);
            
            if (!isset($installerData['requirements_checked'])) {
                return redirect()->route('installer.step', 'requirements');
            } elseif (!isset($installerData['database'])) {
                return redirect()->route('installer.step', 'database');
            } elseif (!isset($installerData['site'])) {
                return redirect()->route('installer.step', 'site');
            } elseif (!isset($installerData['admin'])) {
                return redirect()->route('installer.step', 'admin');
            }
            
            return redirect()->route('installer.index');
        }

        $data = session('installer_data', []);

        return match ($step) {
            'requirements' => view('installer::requirements', ['requirements' => $this->installationService->checkRequirements()]),
            'database' => view('installer::database', array_merge($this->getEnvDatabaseValues(), $data)),
            'site' => view('installer::site', array_merge($this->getEnvSiteValues(), $data)),
            'admin' => view('installer::admin', $data),
            'email' => view('installer::email', array_merge($this->getEnvEmailValues(), $data)),
        };
    }

    /**
     * Get database values from .env file.
     */
    private function getEnvDatabaseValues(): array
    {
        return [
            'db_host' => env('DB_HOST', '127.0.0.1'),
            'db_port' => env('DB_PORT', '3306'),
            'db_database' => env('DB_DATABASE', ''),
            'db_username' => env('DB_USERNAME', 'root'),
            'db_password' => env('DB_PASSWORD', ''),
        ];
    }

    /**
     * Get site values from .env file.
     */
    private function getEnvSiteValues(): array
    {
        return [
            'app_name' => env('APP_NAME', config('app.name')),
            'app_url' => env('APP_URL', 'http://localhost'),
        ];
    }

    /**
     * Get email values from .env file.
     */
    private function getEnvEmailValues(): array
    {
        return [
            'mail_mailer' => env('MAIL_MAILER', 'smtp'),
            'mail_host' => env('MAIL_HOST', ''),
            'mail_port' => env('MAIL_PORT', '587'),
            'mail_username' => env('MAIL_USERNAME', ''),
            'mail_password' => env('MAIL_PASSWORD', ''),
            'mail_encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'mail_from_address' => env('MAIL_FROM_ADDRESS', ''),
        ];
    }

    /**
     * Check if user can access a specific step.
     */
    private function canAccessStep(string $step): bool
    {
        $installerData = session('installer_data', []);

        return match ($step) {
            'requirements' => true, // Always accessible
            'database' => isset($installerData['requirements_checked']) && $installerData['requirements_checked'], // Need requirements to pass
            'site' => isset($installerData['database']), // Need database configured
            'admin' => isset($installerData['database']) && isset($installerData['site']), // Need database and site
            'email' => isset($installerData['database']) && isset($installerData['site']) && isset($installerData['admin']), // Need all previous
            default => false,
        };
    }

    /**
     * Process a specific installation step.
     */
    public function processStep(Request $request, string $step)
    {
        $validSteps = ['requirements', 'database', 'site', 'admin', 'email'];

        if (!in_array($step, $validSteps)) {
            return redirect()->route('installer.index');
        }

        $data = match ($step) {
            'requirements' => $this->processRequirements($request),
            'database' => $this->processDatabase($request),
            'site' => $this->processSite($request),
            'admin' => $this->processAdmin($request),
            'email' => $this->processEmail($request),
        };

        if ($data === true) {
            // Get current installer data
            $installerData = session('installer_data', []);

            // Move to next step
            $stepIndex = array_search($step, $validSteps);
            $nextStep = $validSteps[$stepIndex + 1] ?? 'finalize';

            if ($nextStep === 'finalize') {
                return response()->json(['redirect' => route('installer.finalize')]);
            }

            return response()->json(['redirect' => route('installer.step', $nextStep)]);
        }

        return response()->json($data, 422);
    }

    /**
     * Process requirements check.
     */
    private function processRequirements(Request $request): bool
    {
        $requirements = $this->installationService->checkRequirements();
        $allMet = collect($requirements)->every(fn ($req) => $req['status']);

        if (!$allMet) {
            return ['error' => 'Some system requirements are not met. Please fix them before continuing.'];
        }

        // Store that requirements have been checked and passed
        $data = session('installer_data', []);
        $data['requirements_checked'] = true;
        session(['installer_data' => $data]);

        return $allMet;
    }

    /**
     * Process database configuration.
     */
    private function processDatabase(Request $request): array|bool
    {
        $request->validate([
            'db_host' => 'required|string',
            'db_port' => 'required|integer|between:1,65535',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        $credentials = [
            'host' => $request->db_host,
            'port' => $request->db_port,
            'database' => $request->db_database,
            'username' => $request->db_username,
            'password' => $request->db_password,
        ];

        // Test database connection
        try {
            $this->installationService->testDatabaseConnection($credentials);
        } catch (\Exception $e) {
            return ['error' => 'Database connection failed: ' . $e->getMessage()];
        }

        // Store in session
        $data = session('installer_data', []);
        $data['database'] = $credentials;
        session(['installer_data' => $data]);

        // Update .env file
        $this->installationService->updateEnv([
            'DB_HOST' => $credentials['host'],
            'DB_PORT' => $credentials['port'],
            'DB_DATABASE' => $credentials['database'],
            'DB_USERNAME' => $credentials['username'],
            'DB_PASSWORD' => $credentials['password'],
            'SESSION_DRIVER' => 'file', // Use file sessions until migrations are run
            'CACHE_STORE' => 'file', // Use file cache until migrations are run
            'QUEUE_CONNECTION' => 'sync', // Use sync queue until migrations are run
        ]);

        // Force reconnect to database with new credentials
        DB::purge('mysql');
        DB::reconnect('mysql');

        return true;
    }

    /**
     * Process site configuration.
     */
    private function processSite(Request $request): array|bool
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
        ]);

        $data = session('installer_data', []);
        $data['site'] = [
            'app_name' => $request->app_name,
            'app_url' => $request->app_url,
        ];
        session(['installer_data' => $data]);

        $this->installationService->updateEnv([
            'APP_NAME' => $request->app_name,
            'APP_URL' => $request->app_url,
        ]);

        return true;
    }

    /**
     * Process admin user creation.
     */
    private function processAdmin(Request $request): array|bool
    {
        $request->validate([
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email',
            'workspace_name' => 'required|string|max:255',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        $data = session('installer_data', []);
        $data['admin'] = [
            'name' => $request->admin_name,
            'email' => $request->admin_email,
            'workspace_name' => $request->workspace_name,
        ];
        session(['installer_data' => $data]);

        // Store admin credentials temporarily for finalization
        session(['admin_credentials' => [
            'name' => $request->admin_name,
            'email' => $request->admin_email,
            'password' => $request->admin_password,
            'workspace_name' => $request->workspace_name,
        ]]);

        return true;
    }

    /**
     * Process email configuration.
     */
    private function processEmail(Request $request): array|bool
    {
        $request->validate([
            'mail_mailer' => 'nullable|string',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|in:tls,ssl',
            'mail_from_address' => 'nullable|email',
        ]);

        $data = session('installer_data', []);
        $data['email'] = [
            'mailer' => $request->mail_mailer,
            'host' => $request->mail_host,
            'port' => $request->mail_port,
            'username' => $request->mail_username,
            'encryption' => $request->mail_encryption,
            'from_address' => $request->mail_from_address,
        ];
        session(['installer_data' => $data]);

        $envUpdate = ['MAIL_MAILER' => $request->mail_mailer];

        if ($request->mail_host) {
            $envUpdate['MAIL_HOST'] = $request->mail_host;
        }
        if ($request->mail_port) {
            $envUpdate['MAIL_PORT'] = $request->mail_port;
        }
        if ($request->mail_username) {
            $envUpdate['MAIL_USERNAME'] = $request->mail_username;
        }
        if ($request->mail_password) {
            $envUpdate['MAIL_PASSWORD'] = $request->mail_password;
        }
        if ($request->mail_encryption) {
            $envUpdate['MAIL_ENCRYPTION'] = $request->mail_encryption;
        }

        $envUpdate['MAIL_FROM_ADDRESS'] = $request->mail_from_address;

        $this->installationService->updateEnv($envUpdate);

        return true;
    }

    /**
     * Finalize the installation.
     */
    public function finalize(Request $request)
    {
        try {
            // Check if installation is already complete to prevent duplicate runs
            if (env('APP_INSTALLED') === 'true' || env('APP_INSTALLED') === true) {
                return redirect()->route('installer.done');
            }

            \Log::info('Starting installation finalization...');

            // Use migrate:fresh to drop all tables and run migrations from scratch
            // This ensures clean installation even if previous attempt failed
            try {
                \Log::info('Running migrate:fresh to ensure clean installation...');
                Artisan::call('migrate:fresh', ['--force' => true, '--seed' => false]);
                \Log::info('Migrations completed successfully');
            } catch (\Exception $e) {
                \Log::error('Migration failed: ' . $e->getMessage());
                throw new \Exception('Database migration failed: ' . $e->getMessage() . '. Please ensure your database credentials are correct and the database is accessible.');
            }

            // Seed the database
            try {
                \Log::info('Seeding database...');
                Artisan::call('db:seed', ['--force' => true]);
                \Log::info('Database seeded successfully');
            } catch (\Exception $e) {
                \Log::error('Database seeding failed: ' . $e->getMessage());
                throw new \Exception('Database seeding failed: ' . $e->getMessage());
            }

            // Create admin user only if not already exists
            $adminCredentials = session('admin_credentials', []);
            if ($adminCredentials) {
                try {
                    \Log::info('Creating admin user...');
                    
                    // Check if admin user already exists
                    $existingUser = User::where('email', $adminCredentials['email'])->where('is_super_admin', true)->first();
                    
                    if (!$existingUser) {
                        $adminUser = $this->installationService->createAdminUser(
                            $adminCredentials['name'],
                            $adminCredentials['email'],
                            $adminCredentials['password']
                        );

                        // Create the first workspace for the admin
                        $workspaceName = $adminCredentials['workspace_name'] ?? 'Main Workspace';
                        $this->installationService->createFirstWorkspace($adminUser, $workspaceName);
                        
                        \Log::info('Admin user and workspace created successfully');
                    } else {
                        \Log::info('Admin user already exists, skipping creation');
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to create admin user: ' . $e->getMessage());
                    throw new \Exception('Failed to create admin user: ' . $e->getMessage());
                }
            }

            // Set session to mark that migrations have been run
            session(['migrations_completed' => true]);

            // Clear session data
            session()->forget(['installer_data', 'admin_credentials']);

            \Log::info('Installation completed successfully');

            return redirect()->route('installer.done');
        } catch (\Exception $e) {
            \Log::error('Installation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('installer::error', [
                'error' => $e->getMessage(),
                'suggestion' => 'If this error persists, try running: php artisan install:reset'
            ]);
        }
    }

    /**
     * Show the installation complete page.
     */
    public function done()
    {
        // Check if migrations have been completed
        if (!session('migrations_completed', false)) {
            return redirect()->route('installer.index');
        }

        return view('installer::done', [
            'appName' => config('app.name'),
            'appUrl' => config('app.url'),
        ]);
    }

    /**
     * Complete the installation - called when user clicks Complete button on done page.
     */
    public function complete()
    {
        try {
            // Switch to database drivers now that migrations have completed
            $this->installationService->updateEnv([
                'APP_INSTALLED' => 'true',
                'SESSION_DRIVER' => 'database',
                'CACHE_STORE' => 'database',
                'QUEUE_CONNECTION' => 'database',
            ]);

            // Remove Installation namespace from composer.json
            $this->installationService->removeInstallationFromComposer();

            // Clear any cache that might have old installer references
            try {
                Artisan::call('cache:clear');
            } catch (\Exception $e) {
                \Log::debug('Cache clear failed during completion: ' . $e->getMessage());
            }

            // Destroy the migrations completed session
            session()->forget('migrations_completed');

            // Return JSON response with redirect URL
            return response()->json(['redirect' => config('app.url') . '/login']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Installation completion failed: ' . $e->getMessage()], 422);
        }
    }
}
