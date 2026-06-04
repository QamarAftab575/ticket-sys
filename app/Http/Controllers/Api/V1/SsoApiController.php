<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiTokenService;
use App\Services\SsoJwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SsoApiController extends Controller
{
    protected SsoJwtService $ssoService;
    protected ApiTokenService $tokenService;

    public function __construct(SsoJwtService $ssoService, ApiTokenService $tokenService)
    {
        $this->ssoService = $ssoService;
        $this->tokenService = $tokenService;
    }

    /**
     * Generate SSO JWT token for authenticated user
     * Partner applications use this to get a signed token
     * 
     * POST /api/v1/sso/token
     */
    public function generateToken(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'expires_in' => ['sometimes', 'integer', 'min:60', 'max:3600'], // 1 min to 1 hour
            'organization_id' => ['sometimes', 'uuid', 'exists:organizations,id'],
            'redirect_url' => ['sometimes', 'url'],
        ]);

        // Verify user has access to organization if provided
        if (isset($validated['organization_id'])) {
            $organization = \App\Models\Organization::find($validated['organization_id']);
            if (!$organization || !$organization->hasMember($user)) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'error' => 'You are not a member of this organization',
                ], 403);
            }
        }

        $token = $this->ssoService->generateSsoToken($user, $validated);

        // Generate login URL with token
        $loginUrl = url('/sso/login?token=' . $token);

        return response()->json([
            'message' => 'SSO token generated successfully',
            'token' => $token,
            'login_url' => $loginUrl,
            'expires_in' => $validated['expires_in'] ?? 3600,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Exchange JWT token for API token
     * This allows partner apps to get long-lived API tokens
     * 
     * POST /api/v1/sso/exchange
     */
    public function exchangeToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'jwt_token' => ['required', 'string'],
        ]);

        $result = $this->ssoService->exchangeJwtForApiToken($validated['jwt_token']);

        if (!$result) {
            return response()->json([
                'message' => 'Invalid or expired JWT token',
            ], 401);
        }

        return response()->json([
            'message' => 'Token exchange successful',
            'api_token' => $result['api_token'],
            'expires_at' => $result['token_expires_at']?->toIso8601String(),
            'user' => [
                'id' => $result['user']->id,
                'name' => $result['user']->name,
                'email' => $result['user']->email,
            ],
        ]);
    }

    /**
     * Request SSO token for a specific user (admin only)
     * This allows workspace owners to generate SSO tokens for their members
     * 
     * POST /api/v1/sso/token/user/{user}
     */
    public function generateTokenForUser(Request $request, User $user): JsonResponse
    {
        $currentUser = Auth::user();

        // Validate request
        $validated = $request->validate([
            'organization_id' => ['required', 'uuid', 'exists:organizations,id'],
            'expires_in' => ['sometimes', 'integer', 'min:60', 'max:3600'],
            'redirect_url' => ['sometimes', 'url'],
        ]);

        $organization = \App\Models\Organization::find($validated['organization_id']);

        // Check if current user is workspace owner/admin
        if (!$organization->isAdmin($currentUser)) {
            return response()->json([
                'message' => 'Forbidden',
                'error' => 'Only workspace administrators can generate SSO tokens for users',
            ], 403);
        }

        // Check if target user is member of organization
        if (!$organization->hasMember($user)) {
            return response()->json([
                'message' => 'Forbidden',
                'error' => 'User is not a member of this organization',
            ], 403);
        }

        $token = $this->ssoService->generateSsoToken($user, $validated);
        $loginUrl = url('/sso/login?token=' . $token);

        return response()->json([
            'message' => 'SSO token generated successfully',
            'token' => $token,
            'login_url' => $loginUrl,
            'expires_in' => $validated['expires_in'] ?? 3600,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Validate SSO token without consuming it
     * 
     * POST /api/v1/sso/validate
     */
    public function validateToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        try {
            $payload = $this->ssoService->validateSsoToken($validated['token']);

            if (!$payload) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Invalid or expired token',
                ], 401);
            }

            return response()->json([
                'valid' => true,
                'payload' => [
                    'user_id' => $payload['sub'],
                    'email' => $payload['email'],
                    'name' => $payload['name'],
                    'issued_at' => date('Y-m-d\TH:i:s\Z', $payload['iat']),
                    'expires_at' => date('Y-m-d\TH:i:s\Z', $payload['exp']),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Token validation failed',
            ], 401);
        }
    }
}
