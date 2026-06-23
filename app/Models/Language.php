<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'name',
        'code',
        'direction',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Scope to get only active languages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get the default language
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true)->first();
    }

    /**
     * Get the path to the language's translation folder
     */
    public function getLanguageFolderPath(): string
    {
        return resource_path("lang/{$this->code}");
    }

    /**
     * Get the path to the language's messages.php file
     */
    public function getMessagesFilePath(): string
    {
        return $this->getLanguageFolderPath() . '/messages.php';
    }

    /**
     * Check if language folder exists
     */
    public function folderExists(): bool
    {
        return is_dir($this->getLanguageFolderPath());
    }

    /**
     * Check if messages file exists
     */
    public function messagesFileExists(): bool
    {
        return file_exists($this->getMessagesFilePath());
    }
}
