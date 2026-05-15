<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMember extends Model
{
    use HasUuids;

    protected $table = 'project_members';

    protected $fillable = [
        'project_id',
        'user_id',
        'role',
        'access_type',
        'invited_by',
        'joined_at',
        'assigned_at',
        'assigned_by',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'assigned_at' => 'datetime',
    ];

    // Role constants — project level
    const ROLE_PROJECT_ADMIN = 'project_admin';
    const ROLE_EDITOR = 'editor';
    const ROLE_COMMENTER = 'commenter';
    const ROLE_VIEWER = 'viewer';

    // Access type constants
    const ACCESS_TYPE_WORKSPACE_MEMBER = 'workspace_member';
    const ACCESS_TYPE_DIRECT_INVITE = 'direct_invite';

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function scopeDirectInvites($query)
    {
        return $query->where('access_type', self::ACCESS_TYPE_DIRECT_INVITE);
    }

    public function scopeWorkspaceMembers($query)
    {
        return $query->where('access_type', self::ACCESS_TYPE_WORKSPACE_MEMBER);
    }

    public function scopeByAccessType($query, string $accessType)
    {
        return $query->where('access_type', $accessType);
    }

    public function isWorkspaceMember(): bool
    {
        return $this->access_type === self::ACCESS_TYPE_WORKSPACE_MEMBER;
    }

    public function isDirectInvite(): bool
    {
        return $this->access_type === self::ACCESS_TYPE_DIRECT_INVITE;
    }

    public function getAccessDescription(): string
    {
        return match($this->access_type) {
            self::ACCESS_TYPE_WORKSPACE_MEMBER => 'Workspace member with project access',
            self::ACCESS_TYPE_DIRECT_INVITE => 'External collaborator (guest)',
            default => 'Unknown access type',
        };
    }

    public function isProjectAdmin(): bool
    {
        return $this->role === self::ROLE_PROJECT_ADMIN;
    }

    public function isEditor(): bool
    {
        return $this->role === self::ROLE_EDITOR;
    }

    public function isCommenter(): bool
    {
        return $this->role === self::ROLE_COMMENTER;
    }

    public function isViewer(): bool
    {
        return $this->role === self::ROLE_VIEWER;
    }

    public function canEditProject(): bool
    {
        return in_array($this->role, [self::ROLE_PROJECT_ADMIN, self::ROLE_EDITOR]);
    }

    public function canManageMembers(): bool
    {
        return $this->role === self::ROLE_PROJECT_ADMIN;
    }

    public function canEditTasks(): bool
    {
        return in_array($this->role, [self::ROLE_PROJECT_ADMIN, self::ROLE_EDITOR]);
    }

    public function canComment(): bool
    {
        return in_array($this->role, [self::ROLE_PROJECT_ADMIN, self::ROLE_EDITOR, self::ROLE_COMMENTER]);
    }
}
