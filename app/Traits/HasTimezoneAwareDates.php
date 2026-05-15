<?php

namespace App\Traits;

/**
 * Trait HasTimezoneAwareDates
 * 
 * Ensures consistent datetime handling across models.
 * All datetime fields are automatically cast to UTC for storage
 * and can be converted to user's timezone on the frontend.
 * 
 * Usage:
 * use HasTimezoneAwareDates;
 * 
 * protected $dateFields = ['completed_at', 'edited_at'];
 */
trait HasTimezoneAwareDates
{
    /**
     * Get the attributes that should be cast.
     * Automatically adds datetime casting for common timestamp fields.
     *
     * @return array<string, string>
     */
    protected function getDefaultDateCasts(): array
    {
        $casts = [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];

        // Add soft delete timestamp if the model uses SoftDeletes
        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this))) {
            $casts['deleted_at'] = 'datetime';
        }

        // Add custom date fields defined in the model
        if (property_exists($this, 'dateFields') && is_array($this->dateFields)) {
            foreach ($this->dateFields as $field) {
                $casts[$field] = 'datetime';
            }
        }

        return $casts;
    }

    /**
     * Initialize the trait.
     * Merges default date casts with model-specific casts.
     */
    protected function initializeHasTimezoneAwareDates(): void
    {
        $this->mergeCasts($this->getDefaultDateCasts());
    }
}
