<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'avatar',
        'timezone',
        'is_suspended',
        'last_login_at',
        'must_set_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_suspended' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Set the user's name (store in lowercase).
     */
    protected function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = strtolower($value);
    }

    /**
     * Set the user's email (store in lowercase).
     */
    protected function setEmailAttribute(string $value): void
    {
        $this->attributes['email'] = strtolower($value);
    }

    /**
     * Get the user's avatar as a full public URL.
     * Returns null if no avatar is set.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }

    /**
     * Override avatar serialization to always return a full URL.
     * This ensures all frontend components receive a usable src.
     */
    public function getAvatarAttribute(?string $value): ?string
    {
        if (!$value) return null;
        // Already a full URL (e.g. Google OAuth avatar)
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }
        return asset('storage/' . $value);
    }

    /**
     * Get the user's full name (capitalized).
     */
    public function getFullNameAttribute(): string
    {
        return ucwords($this->name);
    }

    /**
     * Get the user's first name (capitalized).
     */
    public function getFirstNameAttribute(): string
    {
        $parts = explode(' ', $this->name);
        return ucfirst($parts[0] ?? '');
    }

    /**
     * Get the user's last name (capitalized).
     */
    public function getLastNameAttribute(): string
    {
        $parts = explode(' ', $this->name);
        if (count($parts) > 1) {
            return ucfirst(end($parts));
        }
        return '';
    }

    /**
     * Get the user's initials.
     */
    public function getInitialsAttribute(): string
    {
        $parts = explode(' ', $this->name);
        $initials = '';
        foreach ($parts as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }
        return $initials;
    }

    /**
     * Get all organizations this user belongs to.
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_memberships')
            ->select(
                'organizations.id',
                'organizations.name',
                'organizations.avatar_color',
                'organizations.description',
                'organizations.types',
                'organizations.created_at',
                'organizations.updated_at'
            )
            ->withPivot('role', 'joined_at', 'is_active')
            ->withTimestamps();
    }

    /**
     * Get workspace role for a specific organization.
     */
    public function getWorkspaceRole(string $organizationId): ?string
    {
        $membership = $this->organizations()
            ->where('organizations.id', $organizationId)
            ->wherePivot('is_active', true)
            ->first();

        return $membership?->pivot->role;
    }

    /**
     * Check if user is workspace owner.
     */
    public function isWorkspaceOwner(string $organizationId): bool
    {
        return $this->getWorkspaceRole($organizationId) === 'owner';
    }

    /**
     * Check if user is workspace admin.
     */
    public function isWorkspaceAdmin(string $organizationId): bool
    {
        return $this->getWorkspaceRole($organizationId) === 'admin';
    }

    /**
     * Check if user is workspace member (owner, admin, or member - NOT guest).
     */
    public function isWorkspaceMember(string $organizationId): bool
    {
        $role = $this->getWorkspaceRole($organizationId);
        return in_array($role, ['owner', 'admin', 'member']);
    }

    /**
     * Check if user is workspace guest.
     */
    public function isWorkspaceGuest(string $organizationId): bool
    {
        return $this->getWorkspaceRole($organizationId) === 'guest';
    }

    /**
     * Check if user can manage workspace (owner or admin).
     */
    public function canManageWorkspace(string $organizationId): bool
    {
        $role = $this->getWorkspaceRole($organizationId);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Check if user can see all projects in workspace (owner or admin).
     */
    public function canSeeAllProjects(string $organizationId): bool
    {
        $role = $this->getWorkspaceRole($organizationId);
        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Check if user can see public projects in workspace.
     */
    public function canSeePublicProjects(string $organizationId): bool
    {
        $role = $this->getWorkspaceRole($organizationId);
        return in_array($role, ['owner', 'admin', 'member']);
    }

    /**
     * Get all organizations user has access to (including as guest with project access).
     */
    public function getAccessibleOrganizations()
    {
        // Get organizations where user is a member (any role)
        $memberOrgs = $this->organizations()
            ->where('organizations.is_active', true)
            ->wherePivot('is_active', true)
            ->get();

        // Get organizations where user has project access but no membership
        $projectOrgs = Organization::whereHas('projects.members', function ($q) {
            $q->where('user_id', $this->id);
        })
        ->where('is_active', true)
        ->whereNotIn('id', $memberOrgs->pluck('id'))
        ->get();

        return $memberOrgs->merge($projectOrgs);
    }

    /**
     * Get all organization memberships for this user.
     */
    public function organizationMemberships()
    {
        return $this->hasMany(OrganizationMembership::class);
    }

    /**
     * Get all organizations owned by this user.
     */
    public function ownedOrganizations()
    {
        return $this->hasMany(Organization::class, 'created_by');
    }

    /**
     * Get the invitations for this user.
     */
    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'user_id');
    }

    /**
     * Get the invitations sent by this user.
     */
    public function sentInvitations()
    {
        return $this->hasMany(Invitation::class, 'invited_by_user_id');
    }

    /**
     * Check if the user's email is verified.
     */
    public function isEmailVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Check if the user is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->is_suspended;
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(['super-admin', 'admin']);
    }

    /**
     * Scope to get only active (non-deleted) users.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope to get only non-suspended users.
     */
    public function scopeNotSuspended($query)
    {
        return $query->where('is_suspended', false);
    }

    /**
     * Scope to get only verified users.
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope to get only unverified users.
     */
    public function scopeUnverified($query)
    {
        return $query->whereNull('email_verified_at');
    }
}
