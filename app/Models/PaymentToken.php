<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentToken extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'plan_id',
        'token',
        'stripe_session_id',
        'status',
        'amount',
        'currency',
        'expires_at',
        'used_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns this payment token.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plan associated with this payment token.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Generate a unique token
     */
    public static function generateToken(): string
    {
        return hash('sha256', uniqid(rand(), true) . time());
    }

    /**
     * Create a new payment token
     */
    public static function createForPayment(User $user, Plan $plan, string $currency = 'usd'): self
    {
        return self::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'token' => self::generateToken(),
            'status' => 'pending',
            'amount' => $plan->price,
            'currency' => strtolower($currency),
            'expires_at' => now()->addHours(24), // Token expires in 24 hours
        ]);
    }

    /**
     * Check if token is valid
     */
    public function isValid(): bool
    {
        return $this->status === 'pending' && $this->expires_at->gt(now());
    }

    /**
     * Scope to get active/valid tokens
     */
    public function scopeValid($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '>', now());
    }

    /**
     * Mark token as used
     */
    public function markAsUsed(): void
    {
        $this->update([
            'status' => 'used',
            'used_at' => now(),
        ]);
    }

    /**
     * Mark token as expired
     */
    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired',
        ]);
    }

    /**
     * Find token by string
     */
    public static function findByToken(string $token): ?self
    {
        return self::where('token', $token)->first();
    }
}
