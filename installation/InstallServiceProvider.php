<?php

namespace Installation;

use Illuminate\Support\ServiceProvider;

class InstallServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * Load the installer routes and views only when the application
     * has not been installed yet. Once installed, this provider
     * effectively does nothing.
     */
    public function boot(): void
    {
        // Only load installer routes and views if app is not installed
        if (!config('app.installed', false)) {
            $this->loadRoutesFrom(__DIR__ . '/routes.php');
            $this->loadViewsFrom(__DIR__ . '/resources/views', 'installer');
            $this->publishAssets();
        }
    }

    /**
     * Publish installer assets to public directory.
     */
    private function publishAssets(): void
    {
        $this->publishes([
            __DIR__ . '/resources/css' => resource_path('css/installer'),
        ], 'installer-css');

        $this->publishes([
            __DIR__ . '/resources/js' => resource_path('js/installer'),
        ], 'installer-js');
    }
}
