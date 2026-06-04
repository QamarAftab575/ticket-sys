<?php

namespace App\Services;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiTokenService
{
    /**
     * Create a new API token
     */
    public function createToken(User $user, array $data): array
    {
        $plainToken = ApiToken::generateToken();
        $hashedToken = ApiToken::hashToken($plainToken);

        $token = $user->apiTokens()->create([
            'name' => $data['name'],
            'token' => $hashedToken,
            'scopes' => $data['scopes'] ?? [],
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => true,
        ]);

        return [
            'token' => $token,
            'plain_token' => $plainToken, // Only returned once
        ];
    }

    /**
     * Get all tokens for a user (paginated) - includes revoked tokens
     * Optimized: Order by ID instead of created_at for faster queries
     */
    public function getUserTokens(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->apiTokens()
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Get token by ID for a specific user
     */
    public function getTokenById(User $user, int $tokenId): ?ApiToken
    {
        return $user->apiTokens()
            ->whereKey($tokenId)
            ->first();
    }

    /**
     * Validate and find a token by plain token string
     * Returns token with user relationship loaded for authentication
     */
    public function validateToken(string $plainToken): ?ApiToken
    {
        $hashedToken = ApiToken::hashToken($plainToken);

        $token = ApiToken::where('token', $hashedToken)
            ->where('is_active', true)
            ->with('user') // Load user relationship for authentication
            ->select(['id', 'user_id', 'name', 'token', 'scopes', 'expires_at', 'is_active', 'last_used_at', 'created_at'])
            ->first();

        if ($token && !$token->isExpired()) {
            return $token;
        }

        return null;
    }

    /**
     * Revoke a token - bulk update
     */
    public function revokeToken(User $user, int $tokenId): bool
    {
        return (bool) $user->apiTokens()
            ->whereKey($tokenId)
            ->update(['is_active' => false]);
    }

    /**
     * Revoke all tokens for a user - single query
     */
    public function revokeAllTokens(User $user): int
    {
        return $user->apiTokens()
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    /**
     * Format token for API response
     */
    public function formatTokenForResponse(ApiToken $token): array
    {
        return [
            'id' => $token->id,
            'name' => $token->name,
            'masked_token' => $token->getMaskedToken(),
            'created_at' => $token->created_at->toIso8601String(),
            'last_used_at' => $token->last_used_at?->toIso8601String(),
            'expires_at' => $token->expires_at?->toIso8601String(),
            'is_active' => $token->is_active,
            'is_expired' => $token->isExpired(),
        ];
    }

    /**
     * Get token stats for user
     * Optimized: Single query with aggregates instead of multiple queries
     */
    public function getTokenStats(User $user): array
    {
        $stats = $user->apiTokens()
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
            ->first();

        // Count expired tokens (must be calculated in PHP as it requires isExpired() check)
        $expired = $user->apiTokens()
            ->where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->count();

        return [
            'total' => (int) ($stats->total ?? 0),
            'active' => (int) ($stats->active ?? 0),
            'expired' => $expired,
        ];
    }
}
