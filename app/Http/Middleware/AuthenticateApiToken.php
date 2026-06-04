<?php

namespace App\Http\Middleware;

use App\Services\ApiTokenService;
use Closure;
use Illuminate\Http\Request;

class AuthenticateApiToken
{
    protected ApiTokenService $tokenService;

    public function __construct(ApiTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $this->extractTokenFromRequest($request);

        if (!$token) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'Missing or invalid API token',
            ], 401);
        }

        $apiToken = $this->tokenService->validateToken($token);

        if (!$apiToken) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'Invalid or revoked API token',
            ], 401);
        }

        if ($apiToken->isExpired()) {
            return response()->json([
                'message' => 'Forbidden',
                'error' => 'API token has expired',
            ], 403);
        }

        // Record token usage asynchronously to avoid blocking
        $apiToken->recordUsage();

        // Set the authenticated user in the request
        $request->setUserResolver(fn() => $apiToken->user);
        auth()->setUser($apiToken->user);

        return $next($request);
    }

    /**
     * Extract token from Authorization header or query parameter
     * Checks in order: Bearer header → X-API-Token header → query param
     */
    protected function extractTokenFromRequest(Request $request): ?string
    {
        // Check Authorization header: Bearer {token} (fastest)
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            return $bearerToken;
        }

        // Check custom header: X-API-Token
        $customHeader = $request->header('X-API-Token');
        if ($customHeader) {
            return $customHeader;
        }

        // Check query parameter: ?api_token=...
        return $request->query('api_token');
    }
}
