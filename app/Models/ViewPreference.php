<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViewPreference extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'project_id',
        'view_type',
        'filters',
        'sort',
        'grouping',
        'column_widths',
        'hidden_columns',
        'collapsed_sections',
        'card_fields',
        'zoom_level',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'sort' => 'array',
            'column_widths' => 'array',
            'hidden_columns' => 'array',
            'collapsed_sections' => 'array',
            'card_fields' => 'array',
        ];
    }

    /**
     * Get the user that owns the view preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project that the view preference belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
