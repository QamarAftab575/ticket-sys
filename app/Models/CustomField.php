<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomField extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'project_id',
        'user_id',
        'name',
        'field_type',
        'options',
        'is_global',
        'is_active',
        'position',
        'field_scope',
    ];

    protected $casts = [
        'options'    => 'array',
        'is_global'  => 'boolean',
        'is_active'  => 'boolean',
        'position'   => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Field scopes
    public const SCOPE_PROJECT = 'project';
    public const SCOPE_PERSONAL = 'personal';
    public const SCOPE_WORKSPACE = 'workspace';

    // Field types that support options (select lists)
    public const SELECT_TYPES = ['single_select', 'multi_select',];

    /**
     * Scope: Get only active fields
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get fields by scope
     */
    public function scopeByScope($query, string $scope)
    {
        return $query->where('field_scope', $scope);
    }

    /**
     * Scope: Get project-scoped fields for a specific project
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('field_scope', self::SCOPE_PROJECT)
                     ->where('project_id', $projectId);
    }

    /**
     * Scope: Get personal-scoped fields for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('field_scope', self::SCOPE_PERSONAL)
                     ->where('user_id', $userId);
    }

    /**
     * Get the project that owns the custom field (if project-scoped).
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who created the personal custom field.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the custom field values for this field.
     */
    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }
}
