<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\TranslationHelper;
use Symfony\Component\HttpFoundation\Response;

/**
 * SetLocale Middleware
 * 
 * Automatically sets the application locale based on cached site-wide language
 * Runs on every request to ensure consistent language across the application
 */
class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = TranslationHelper::getSiteLanguage();
        
        app()->setLocale($locale);
        
        return $next($request);
    }
}
