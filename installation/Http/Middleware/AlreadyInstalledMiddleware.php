<?php

namespace Installation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AlreadyInstalledMiddleware
{
    /**
     * Handle an incoming request.
     *
     * If the app is already installed, redirect to home page.
     * Otherwise, allow access to the installer.
     */
    public function handle(Request $request, Closure $next)
    {
        // Ensure session is started for installer
        session()->start();
        
        // Allow finalize, done, and complete routes to always run regardless of installation status
        if ($request->is('install/finalize') || $request->is('install/complete') || $request->is('install/done')) {
            return $next($request);
        }
        
        // For other routes, check if already installed
        if (config('app.installed', false)) {
            return redirect('/');
        }

        return $next($request);
    }
}

