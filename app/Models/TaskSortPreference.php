<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskSortPreference extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'project_id',
        'sort_criteria',
    ];

    protected $casts = [
        'sort_criteria' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the sort preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project that owns the sort preference.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
