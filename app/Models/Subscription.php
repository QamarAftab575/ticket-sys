<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'plan_id',
        'source',
        'started_at',
        'expires_at',
        'status',
        'stripe_subscription_id',
        'price_paid',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'price_paid' => 'decimal:2',
    ];

    /**
     * Get the user that owns this subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plan associated with this subscription.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Scope to get active subscriptions only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope to get expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->where('status', 'active')
                  ->whereNotNull('expires_at')
                  ->where('expires_at', '<', now());
            });
    }

    /**
     * Get the user's current active subscription.
     */
    public static function getCurrentSubscription(User $user)
    {
        return self::where('user_id', $user->id)
            ->active()
            ->latest('started_at')
            ->first();
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expires_at && $this->expires_at < now()) {
            return false;
        }

        return true;
    }

    /**
     * Mark subscription as expired.
     */
    public function markAsExpired()
    {
        $this->update([
            'status' => 'expired',
            'expires_at' => now(),
        ]);
    }
}
