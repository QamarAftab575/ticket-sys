<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MyTaskViewPreference extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'view_type',
        'sort',
        'grouping',
        'section_order',
        'collapsed_sections',
        'filters',
    ];

    protected function casts(): array
    {
        return [
            'sort'               => 'array',
            'section_order'      => 'array',
            'collapsed_sections' => 'array',
            'filters'            => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
