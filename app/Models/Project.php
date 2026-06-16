<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'organization_id',
        'manager_id',
        'owner_id',
        'created_by',
        'status',
        'status_update',
        'visibility',
        'workspace_member_role',
        'privacy',
        'color',
        'icon',
        'start_date',
        'target_date',
        'archived_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
        'start_date'  => 'date',
        'target_date' => 'date',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role', 'access_type', 'invited_by', 'assigned_at', 'assigned_by')
            ->withTimestamps();
    }

    public function projectMembers(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ProjectInvitation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the project manager/lead (Project Lead).
     * The manager is responsible for overseeing the project.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function customFields(): HasMany
    {
        return $this->hasMany(CustomField::class)->orderBy('position');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProjectActivity::class);
    }

    public function attachments(): HasManyThrough
    {
        return $this->hasManyThrough(Attachment::class, Task::class);
    }

    // Visibility: public_to_team | private_to_members (from `visibility` column)
    public function scopePublicToTeam($query)
    {
        return $query->where('visibility', 'public_to_team');
    }

    public function scopePrivateToMembers($query)
    {
        return $query->where('visibility', 'private_to_members');
    }

    public function scopeVisibleTo($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where(function ($subQ) use ($user) {
                // Check if user is workspace owner or admin - they see ALL projects
                $subQ->whereHas('organization.memberships', function ($memberQ) use ($user) {
                    $memberQ->where('user_id', $user->id)
                        ->where('is_active', true)
                        ->whereIn('role', ['owner', 'admin']);
                });
            })
            ->orWhere(function ($subQ) use ($user) {
                // Check if user is workspace member - they see PUBLIC projects
                $subQ->where('visibility', 'public_to_team')
                    ->whereHas('organization.memberships', function ($memberQ) use ($user) {
                        $memberQ->where('user_id', $user->id)
                            ->where('is_active', true)
                            ->where('role', 'member');
                    });
            })
            ->orWhere(function ($subQ) use ($user) {
                // Check if user is explicitly added to project (covers private projects, guests, and direct invites)
                $subQ->whereHas('members', function ($memberQ) use ($user) {
                    $memberQ->where('user_id', $user->id);
                });
            });
        });
    }

    public function isPublicToTeam(): bool
    {
        return $this->visibility === 'public_to_team';
    }

    public function isPrivate(): bool
    {
        return $this->visibility === 'private_to_members';
    }

    public function hasMember(User $user): bool
    {
        return $this->members()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function getMemberRole(User $user): ?string
    {
        return $this->projectMembers()
            ->where('user_id', $user->id)
            ->value('role');
    }
}
