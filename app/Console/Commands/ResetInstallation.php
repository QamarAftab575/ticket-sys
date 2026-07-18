<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ResetInstallation extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'install:reset {--force : Force the reset without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Reset a failed installation by dropping all tables and resetting APP_INSTALLED flag';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Safety check - only allow if APP_INSTALLED is false or this is explicitly forced
        if (config('app.installed', false) && !$this->option('force')) {
            $this->error('Application is marked as installed. Use --force to reset anyway.');
            $this->info('To reset: php artisan install:reset --force');
            return 1;
        }

        // Confirm with user
        if (!$this->option('force')) {
            $this->warn('⚠️  This will drop ALL tables in the database and reset the installation.');
            $this->info('Database: ' . config('database.connections.mysql.database'));
            
            if (!$this->confirm('Are you sure you want to continue?')) {
                $this->info('Reset cancelled.');
                return 0;
            }
        }

        try {
            $this->info('🔄 Resetting installation...');
            $this->newLine();

            // Drop all tables using migrate:fresh
            $this->info('📦 Dropping all tables...');
            \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--force' => true, '--drop-views' => true]);
            $this->info('✓ All tables dropped');

            // Update .env to mark as not installed
            $this->info('📝 Updating .env file...');
            $this->updateEnv(['APP_INSTALLED' => 'false']);
            $this->info('✓ APP_INSTALLED set to false');

            // Clear all caches
            $this->info('🧹 Clearing caches...');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            $this->info('✓ Caches cleared');

            $this->newLine();
            $this->info('✅ Installation has been reset successfully!');
            $this->newLine();
            $this->info('👉 You can now visit /install to start fresh.');

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Failed to reset installation: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Manual reset required:');
            $this->info('1. Drop all tables in your database');
            $this->info('2. Set APP_INSTALLED=false in .env');
            $this->info('3. Visit /install');
            return 1;
        }
    }

    /**
     * Update .env file.
     */
    private function updateEnv(array $values): void
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            throw new \Exception('.env file not found');
        }

        $envContent = File::get($envPath);

        foreach ($values as $key => $value) {
            $escapedValue = addcslashes($value, '\\$"');

            if (preg_match("/^" . preg_quote($key) . "=/m", $envContent)) {
                $envContent = preg_replace(
                    "/^" . preg_quote($key) . "=.*/m",
                    $key . "=" . $escapedValue,
                    $envContent
                );
            } else {
                $envContent .= "\n" . $key . "=" . $escapedValue;
            }
        }

        File::put($envPath, $envContent);
    }
}
