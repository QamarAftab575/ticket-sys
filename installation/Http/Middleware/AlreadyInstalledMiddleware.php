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
        if (config('app.installed', false)) {
            return redirect('/');
        }

        return $next($request);
    }
}
