<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApiTokenRequest;
use App\Services\ApiTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ApiTokenController extends Controller
{
    protected ApiTokenService $apiTokenService;

    public function __construct(ApiTokenService $apiTokenService)
    {
        $this->apiTokenService = $apiTokenService;
    }

    /**
     * Show API tokens management page
     */
    public function show(): Response
    {
        $user = auth()->user();

        // Check access: only Owner/Admin in any workspace, or global Admin
        $canAccess = $user->hasRole(['super-admin', 'admin'])
            || $user->organizations()
                ->wherePivot('is_active', true)
                ->wherePivotIn('role', ['owner', 'admin'])
                ->exists();

        if (!$canAccess) {
            abort(403, 'You do not have permission to manage API tokens.');
        }

        $tokens = $this->apiTokenService->getUserTokens($user, 15);
        $stats = $this->apiTokenService->getTokenStats($user);
        
        // Get user workspaces for sidebar
        $userWorkspaces = $user->organizations()
            ->where('organizations.is_active', true)
            ->wherePivot('is_active', true)
            ->select('organizations.id', 'organizations.name', 'organizations.avatar_color')
            ->get();

        return Inertia::render('Settings/Integrations/ApiTokens', [
            'tokens' => $tokens->map(fn($t) => $this->apiTokenService->formatTokenForResponse($t))->values(),
            'stats' => $stats,
            'userWorkspaces' => $userWorkspaces,
            'currentWorkspace' => null,
            'userRole' => 'member',
        ]);
    }

    /**
     * Create a new API token
     */
    public function store(StoreApiTokenRequest $request): JsonResponse
    {
        $user = auth()->user();

        $result = $this->apiTokenService->createToken($user, $request->validated());

        return response()->json([
            'token' => $this->apiTokenService->formatTokenForResponse($result['token']),
            'plain_token' => $result['plain_token'],
            'message' => 'API token created successfully. Copy the token below and keep it safe.',
        ]);
    }

    /**
     * Revoke an API token
     */
    public function destroy(int $tokenId): JsonResponse
    {
        $user = auth()->user();

        $success = $this->apiTokenService->revokeToken($user, $tokenId);

        if (!$success) {
            return response()->json([
                'message' => 'Token not found',
            ], 404);
        }

        return response()->json([
            'message' => 'API token revoked successfully',
        ]);
    }

    /**
     * Permanently delete an API token
     */
    public function delete(int $tokenId): JsonResponse
    {
        $user = auth()->user();

        $token = $this->apiTokenService->getTokenById($user, $tokenId);

        if (!$token) {
            return response()->json([
                'message' => 'Token not found',
            ], 404);
        }

        $token->forceDelete();

        return response()->json([
            'message' => 'API token deleted permanently',
        ]);
    }

    /**
     * Revoke all tokens
     */
    public function revokeAll(): JsonResponse
    {
        $user = auth()->user();

        $count = $this->apiTokenService->revokeAllTokens($user);

        return response()->json([
            'message' => "All $count API tokens have been revoked",
        ]);
    }

    /**
     * Get tokens list (for AJAX pagination)
     */
    public function list(): JsonResponse
    {
        $user = auth()->user();
        $tokens = $this->apiTokenService->getUserTokens($user);

        return response()->json([
            'tokens' => $tokens->map(fn($t) => $this->apiTokenService->formatTokenForResponse($t))->toArray(),
            'pagination' => [
                'current_page' => $tokens->currentPage(),
                'per_page' => $tokens->perPage(),
                'total' => $tokens->total(),
                'path' => $tokens->path(),
                'next_page_url' => $tokens->nextPageUrl(),
                'prev_page_url' => $tokens->previousPageUrl(),
            ],
        ]);
    }
}
