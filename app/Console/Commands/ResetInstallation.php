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
            return 1;
        }

        // Confirm with user
        if (!$this->option('force')) {
            if (!$this->confirm('This will drop ALL tables in the database. Are you sure?')) {
                $this->info('Reset cancelled.');
                return 0;
            }
        }

        try {
            $this->info('Resetting installation...');

            // Drop all tables
            $this->dropAllTables();

            // Update .env to mark as not installed
            $this->updateEnv(['APP_INSTALLED' => 'false']);

            $this->info('✓ All tables dropped');
            $this->info('✓ APP_INSTALLED set to false');
            $this->info('');
            $this->info('Installation has been reset. You can now visit /install to start fresh.');

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to reset installation: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Drop all tables in the database.
     */
    private function dropAllTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = DB::select('SHOW TABLES');
        $databaseName = config('database.connections.mysql.database');

        foreach ($tables as $table) {
            $tableName = $table->{"Tables_in_{$databaseName}"};
            DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
            $this->line("  Dropped table: {$tableName}");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
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
