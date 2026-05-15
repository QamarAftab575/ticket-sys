<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Publish spatie/laravel-permission migrations if not already published
        $this->publishPermissionsMigrations();
    }

    protected function publishPermissionsMigrations(): void
    {
        // Check if permissions table migration exists
        $migrationsPath = database_path('migrations');
        $permissionsMigrationExists = false;

        foreach (glob($migrationsPath . '/*create_permission_tables.php') as $file) {
            $permissionsMigrationExists = true;
            break;
        }

        if (!$permissionsMigrationExists) {
            // Publish the migrations
            $this->artisan('vendor:publish', [
                '--provider' => 'Spatie\\Permission\\PermissionServiceProvider',
                '--tag' => 'permission-migrations',
                '--force' => true,
            ]);
        }
    }
}
