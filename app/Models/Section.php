<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Section extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'project_id',
        'user_id',
        'name',
        'position',
        'is_my_tasks',
    ];

    protected $casts = [
        'position'    => 'integer',
        'is_my_tasks' => 'boolean',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /**
     * Get the project that owns the section.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user that owns this My Tasks section.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to only My Tasks sections for a given user.
     */
    public function scopeMyTasks($query, string $userId)
    {
        return $query->where('is_my_tasks', true)->where('user_id', $userId);
    }

    /**
     * Get the tasks in this section.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
