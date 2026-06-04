<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;

class SsoJwtService
{
    /**
     * Generate a signed JWT token for SSO
     * This token is used by partner applications to authenticate users
     */
    public function generateSsoToken(User $user, array $options = []): string
    {
        $payload = [
            'iss' => config('app.url'), // Issuer
            'sub' => $user->id, // Subject (user ID)
            'email' => $user->email,
            'name' => $user->name,
            'iat' => time(), // Issued at
            'exp' => time() + ($options['expires_in'] ?? 3600), // Expires in 1 hour by default
            'jti' => bin2hex(random_bytes(16)), // JWT ID (unique identifier)
        ];

        // Add custom claims if provided
        if (isset($options['organization_id'])) {
            $payload['organization_id'] = $options['organization_id'];
        }

        if (isset($options['redirect_url'])) {
            $payload['redirect_url'] = $options['redirect_url'];
        }

        // Sign the JWT with secret key
        $jwt = JWT::encode($payload, $this->getJwtSecret(), 'HS256');

        // Store JWT ID in cache to track one-time use (prevents replay attacks)
        Cache::put("jwt_used:{$payload['jti']}", true, $payload['exp'] - time());

        return $jwt;
    }

    /**
     * Validate and decode SSO JWT token
     */
    public function validateSsoToken(string $token): ?array
    {
        try {
            // Decode and verify JWT signature
            $decoded = JWT::decode($token, new Key($this->getJwtSecret(), 'HS256'));
            $payload = (array) $decoded;

            // Check if token has already been used (prevent replay attacks)
            if (Cache::has("jwt_used:{$payload['jti']}")) {
                throw new \Exception('Token has already been used');
            }

            // Mark token as used
            Cache::put("jwt_used:{$payload['jti']}", true, $payload['exp'] - time());

            return $payload;
        } catch (\Exception $e) {
            // Token invalid, expired, or already used
            return null;
        }
    }

    /**
     * Generate API token from SSO JWT
     * This exchanges a short-lived JWT for a long-lived API token
     */
    public function exchangeJwtForApiToken(string $jwtToken): ?array
    {
        $payload = $this->validateSsoToken($jwtToken);

        if (!$payload) {
            return null;
        }

        // Find user by ID
        $user = User::find($payload['sub']);

        if (!$user) {
            return null;
        }

        // Create API token for the user
        $apiTokenService = app(ApiTokenService::class);
        $tokenData = $apiTokenService->createToken($user, [
            'name' => 'SSO Token',
            'scopes' => ['*'],
            'expires_at' => now()->addDays(30), // Long-lived token
        ]);

        return [
            'user' => $user,
            'api_token' => $tokenData['plain_token'],
            'token_expires_at' => $tokenData['token']->expires_at,
        ];
    }

    /**
     * Get JWT secret key from environment
     */
    private function getJwtSecret(): string
    {
        $secret = config('app.jwt_secret') ?? env('JWT_SECRET');

        if (!$secret) {
            throw new \Exception('JWT_SECRET not configured. Please add JWT_SECRET to your .env file.');
        }

        return $secret;
    }

    /**
     * Generate a secure JWT secret (for initial setup)
     */
    public static function generateSecret(): string
    {
        return base64_encode(random_bytes(64));
    }
}
