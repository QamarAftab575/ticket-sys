<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskGroupPreference extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'project_id',
        'group_by',
        'collapsed_groups',
    ];

    protected $casts = [
        'collapsed_groups' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the group preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project that owns the group preference.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
