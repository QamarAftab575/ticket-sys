<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'token',
        'plain_token',
        'scopes',
        'last_used_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'scopes' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'token',
        'plain_token',
    ];

    /**
     * Get the user that owns the token
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new secure token
     */
    public static function generateToken(): string
    {
        return 'sk_' . Str::random(64);
    }

    /**
     * Hash a plain token for storage
     */
    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * Check if a plain token matches this token
     */
    public function matchesToken(string $plainToken): bool
    {
        return self::hashToken($plainToken) === $this->token;
    }

    /**
     * Get the masked token display
     */
    public function getMaskedToken(): string
    {
        // Faster masking: only show first 6 and last 6 chars
        return substr($this->token, 0, 6) . '****' . substr($this->token, -6);
    }

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return $this->expires_at->isPast();
    }

    /**
     * Check if token is valid and active
     */
    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Update last used timestamp
     */
    public function recordUsage(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
