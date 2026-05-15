<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsSet
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->must_set_password) {
            // Allow access to password setup routes
            if ($request->routeIs('password.set') || $request->routeIs('logout')) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Password setup required.'], 403);
            }

            return redirect()->route('password.set')
                ->with('warning', 'Please set your password to continue.');
        }

        return $next($request);
    }
}
