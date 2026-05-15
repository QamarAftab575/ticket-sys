<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BusinessSetting extends Model
{
    use HasUuids;

    protected $table = 'business_settings';

    protected $fillable = ['key', 'value', 'description'];

    protected $casts = [
        'value' => 'json',
    ];

    /**
     * Retrieve a business setting by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Save a business setting by key
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Delete a business setting by key
     *
     * @param string $key
     * @return void
     */
    public static function forget(string $key): void
    {
        static::where('key', $key)->delete();
    }
}
