<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectInvitation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'project_id',
        'workspace_id',
        'email',
        'role',
        'invited_by',
        'token',
        'status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    // Project-level role constants
    const ROLE_PROJECT_ADMIN = 'project_admin';
    const ROLE_EDITOR = 'editor';
    const ROLE_COMMENTER = 'commenter';
    const ROLE_VIEWER = 'viewer';

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'workspace_id');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || now()->isAfter($this->expires_at);
    }

    public function isValid(): bool
    {
        return $this->status === 'pending' && now()->isBefore($this->expires_at);
    }

    public function markAccepted(): bool
    {
        return $this->update(['status' => 'accepted']);
    }

    public function markExpired(): bool
    {
        return $this->update(['status' => 'expired']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now());
    }
}
