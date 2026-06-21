<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_cycle',
        'features',
        'max_workspaces',
        'max_members_per_workspace',
        'max_projects_per_workspace',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Check if a limit is unlimited (0 means unlimited).
     */
    public function isUnlimited(string $field): bool
    {
        return $this->{$field} === 0;
    }

    /**
     * Get all users with this plan.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'active_plan_id');
    }
}
