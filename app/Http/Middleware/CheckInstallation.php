<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow installation routes to pass through
        if ($request->is('install*')) {
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
