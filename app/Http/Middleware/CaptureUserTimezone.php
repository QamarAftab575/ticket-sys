<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CaptureUserTimezone
{
    /**
     * Handle an incoming request.
     *
     * Captures the user's timezone from the request header and stores it
     * in the database if it differs from the current stored value.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            
            // Get timezone from request header (sent by frontend)
            $timezone = $request->header('X-Timezone');
            
            // Validate timezone
            if ($timezone && $this->isValidTimezone($timezone)) {
                // Only update if timezone has changed
                if ($user->timezone !== $timezone) {
                    $user->update(['timezone' => $timezone]);
                }
            }
        }

        return $next($request);
    }

    /**
     * Validate if the provided timezone is valid.
     *
     * @param string $timezone
     * @return bool
     */
    private function isValidTimezone(string $timezone): bool
    {
        return in_array($timezone, \DateTimeZone::listIdentifiers());
    }
}
