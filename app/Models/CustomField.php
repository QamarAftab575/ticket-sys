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
        'name',
        'field_type',
        'options',
        'is_global',
        'is_active',
        'position',
    ];

    protected $casts = [
        'options'    => 'array',
        'is_global'  => 'boolean',
        'is_active'  => 'boolean',
        'position'   => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Field types that support options (select lists)
    public const SELECT_TYPES = ['single_select', 'multi_select',];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the project that owns the custom field.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the custom field values for this field.
     */
    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }
}
