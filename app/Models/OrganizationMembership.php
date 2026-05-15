<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationMembership extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'organization_memberships';

    protected $fillable = [
        'organization_id',
        'user_id',
        'role',
        'joined_at',
        'is_active',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Role constants — workspace level
    const ROLE_OWNER = 'owner';
    const ROLE_ADMIN = 'admin';
    const ROLE_MEMBER = 'member';
    const ROLE_GUEST = 'guest';

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isMember(): bool
    {
        return $this->role === self::ROLE_MEMBER;
    }

    public function isGuest(): bool
    {
        return $this->role === self::ROLE_GUEST;
    }

    public function isWorkspaceMember(): bool
    {
        // Owner, Admin, and Member are full workspace members
        // Guest is NOT a full workspace member
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN, self::ROLE_MEMBER]);
    }

    public function canManageWorkspace(): bool
    {
        // Owner and Admin can manage workspace settings and members
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN]);
    }

    public function canDeleteWorkspace(): bool
    {
        // Only Owner can delete workspace
        return $this->role === self::ROLE_OWNER;
    }

    public function canSeeAllProjects(): bool
    {
        // Owner and Admin can see ALL projects (public + private)
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN]);
    }

    public function canSeePublicProjects(): bool
    {
        // Owner, Admin, and Member can see public projects automatically
        // Guest cannot see public projects (only explicitly shared projects)
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN, self::ROLE_MEMBER]);
    }

    public function isActive(): bool
    {
        return $this->is_active !== false;
    }

    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }
}
