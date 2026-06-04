<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ApiTokenService;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    protected AuthService $authService;
    protected ApiTokenService $tokenService;

    public function __construct(AuthService $authService, ApiTokenService $tokenService)
    {
        $this->authService = $authService;
        $this->tokenService = $tokenService;
    }

    /**
     * Register endpoint is disabled for API
     * 
     * Users can only be invited via workspace/project invitations.
     * This prevents unauthorized self-registration through the API.
     * 
     * @group Authentication
     * @response 403 {
     *   "message": "Registration is not available via API",
     *   "error": "Users can only join through workspace or project invitations"
     * }
     * @unauthenticated
     */
    public function register(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Registration is not available via API',
            'error' => 'Users can only join through workspace or project invitations',
        ], 403);
    }

    /**
     * Login and return an API token
     * 
     * Authenticates a user with email and password and returns a long-lived API token
     * for subsequent requests. Rate limited to prevent brute force attacks.
     * 
     * @group Authentication
     * @bodyParam email string required User email address. Example: john@example.com
     * @bodyParam password string required User password (min 8 characters). Example: Password123
     * 
     * @response 200 {
     *   "message": "Login successful",
     *   "user": {
     *     "id": "9d3e4b1a-7f8c-4d2e-9a1b-3c4d5e6f7a8b",
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "email_verified_at": "2024-03-15T10:30:00Z",
     *     "last_login_at": "2024-03-15T10:30:00Z"
     *   },
     *   "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
     * }
     * 
     * @response 401 {
     *   "message": "Invalid credentials"
     * }
     * 
     * @response 429 {
     *   "message": "Too many login attempts. Please try again later."
     * }
     * 
     * @unauthenticated
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Check rate limit
        if (!$this->authService->checkRateLimit($validated['email'])) {
            return response()->json([
                'message' => 'Too many login attempts. Please try again later.',
            ], 429);
        }

        // Attempt login
        if (!$this->authService->login($validated['email'], $validated['password'])) {
            $this->authService->incrementFailedAttempts($validated['email']);
            
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user();

        // Create API token
        $tokenData = $this->tokenService->createToken($user, [
            'name' => 'Login Token',
            'scopes' => ['*'],
        ]);

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'last_login_at' => $user->last_login_at?->toIso8601String(),
            ],
            'token' => $tokenData['plain_token'],
        ]);
    }

    /**
     * Logout (revoke current API token)
     */
    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Get current token from request
        $token = $request->bearerToken() 
            ?? $request->header('X-API-Token') 
            ?? $request->query('api_token');

        if ($token) {
            // Find and revoke the current token
            $apiToken = $this->tokenService->validateToken($token);
            if ($apiToken) {
                $this->tokenService->revokeToken($user, $apiToken->id);
            }
        }

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Request password reset
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $this->authService->requestPasswordReset($validated['email']);

        return response()->json([
            'message' => 'If an account exists with that email, a password reset link has been sent.',
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $success = $this->authService->resetPassword(
                $validated['token'],
                $validated['password']
            );

            if (!$success) {
                return response()->json([
                    'message' => 'Invalid or expired reset token',
                ], 400);
            }

            return response()->json([
                'message' => 'Password reset successful',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Refresh API token (revoke old, create new)
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Get current token
        $token = $request->bearerToken() 
            ?? $request->header('X-API-Token') 
            ?? $request->query('api_token');

        // Revoke current token
        if ($token) {
            $apiToken = $this->tokenService->validateToken($token);
            if ($apiToken) {
                $this->tokenService->revokeToken($user, $apiToken->id);
            }
        }

        // Create new token
        $tokenData = $this->tokenService->createToken($user, [
            'name' => 'Refreshed Token',
            'scopes' => ['*'],
        ]);

        return response()->json([
            'message' => 'Token refreshed successfully',
            'token' => $tokenData['plain_token'],
        ]);
    }
}
