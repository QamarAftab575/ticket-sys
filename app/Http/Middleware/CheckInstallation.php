<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    /**
     * Handle an incoming request.
     *
     * If the app is not installed, redirect to the installation wizard.
     * This check is skipped for:
     * - Installation wizard routes
     * - Health check routes
     * - Debug routes (local only)
     *
     * During installation, we force file-based sessions/cache to avoid
     * attempting database access before migrations are run.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow installation routes to pass through
        if ($request->is('install*')) {
            // During installation, clear any old cache that might interfere
            try {
                Artisan::call('optimize:clear');
            } catch (\Exception $e) {
                // Silently fail - don't disrupt installation flow
                \Log::debug('Cache clear failed during installation: ' . $e->getMessage());
            }

            // During installation, force file-based drivers to prevent
            // "table doesn't exist" errors before migrations are run
            config(['session.driver' => 'file']);
            config(['cache.default' => 'file']);
            config(['queue.default' => 'sync']);
            
            return $next($request);
        }

        // Allow health check
        if ($request->is('up')) {
            return $next($request);
        }

        // Allow debug routes in local environment
        if (app()->isLocal() && ($request->is('run-migration') || $request->is('debug/*'))) {
            return $next($request);
        }

        // If app is not installed, redirect to installer
        if (!config('app.installed', false)) {
            return redirect('/install');
        }

        return $next($request);
    }
}
