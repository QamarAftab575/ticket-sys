<?php

namespace App\Helpers;

use App\Models\Language;
use Illuminate\Support\Facades\Cache;

/**
 * TranslationHelper - Centralized translation management with caching
 *
 * Site-Wide Language Approach:
 *   - Admin sets ONE default language for entire site
 *   - Language cached for 30 days (cleared only on change)
 *   - All users see same language (no per-user switching)
 *   - Single source of truth: resources/lang/{code}/messages.php
 *
 * Usage in Blade/PHP:
 *   trans('messages.dashboard')     // Returns: "Dashboard"
 *   __('messages.welcome')          // Returns: "Welcome"
 *
 * Usage in Vue (translations passed via Inertia):
 *   {{ $t('dashboard') }}           // Returns translated value
 */
class TranslationHelper
{
    /**
     * Cache duration for site language: 30 days (2592000 seconds)
     * Only cleared when admin explicitly changes language
     */
    const SITE_LANGUAGE_CACHE_TTL = 2592000; // 30 days

    /**
     * Cache duration for translations: 7 days
     * Cleared when translations are updated
     */
    const TRANSLATIONS_CACHE_TTL = 604800; // 7 days

    /**
     * Cache duration for active languages list: 1 day
     */
    const LANGUAGES_LIST_CACHE_TTL = 86400; // 1 day

    /**
     * Get site-wide language code (cached for 30 days)
     * This is THE language for the entire application
     */
    public static function getSiteLanguage(): string
    {
        return Cache::remember('site_language', self::SITE_LANGUAGE_CACHE_TTL, function () {
            // Get language from business_settings table
            $languageCode = \App\Models\BusinessSetting::get('default_selected_language');
            
            if ($languageCode) {
                // Verify the language exists and is active
                $language = Language::where('code', $languageCode)
                    ->where('is_active', true)
                    ->first();
                
                if ($language) {
                    return $languageCode;
                }
            }
            
            // Fallback to default language (English)
            $defaultLang = Language::where('is_default', true)->first();
            return $defaultLang ? $defaultLang->code : 'en';
        });
    }

    /**
     * Set site-wide language (admin only)
     * Clears cache and sets new language for entire site
     */
    public static function setSiteLanguage(string $code): void
    {
        // Validate language exists and is active
        if (!Language::where('code', $code)->where('is_active', true)->exists()) {
            throw new \Exception("Language '{$code}' not found or inactive");
        }

        // Save to business_settings table
        \App\Models\BusinessSetting::set('default_selected_language', $code);

        // Clear old cache
        Cache::forget('site_language');
        Cache::forget("translations.{$code}");
        
        // Update app locale immediately
        app()->setLocale($code);
        
        \Log::info("Site language changed to: {$code}");
    }

    /**
     * Get translations for current site language (cached)
     * Returns associative array of key => value pairs
     */
    public static function getSiteTranslations(): array
    {
        $languageCode = self::getSiteLanguage();
        
        return Cache::remember("translations.{$languageCode}", self::TRANSLATIONS_CACHE_TTL, function () use ($languageCode) {
            $language = Language::where('code', $languageCode)->first();
            
            if (!$language || !$language->messagesFileExists()) {
                // Fallback to English
                $englishPath = resource_path('lang/en/messages.php');
                return file_exists($englishPath) ? require $englishPath : [];
            }
            
            return require $language->getMessagesFilePath();
        });
    }

    /**
     * Get all active languages for frontend language selector (cached)
     * Only returns languages where is_active = true
     */
    public static function getActiveLanguages(): array
    {
        return Cache::remember('active_languages', self::LANGUAGES_LIST_CACHE_TTL, function () {
            return Language::where('is_active', true)
                ->select('code', 'name', 'direction')
                ->orderBy('is_default', 'desc')
                ->orderBy('name')
                ->get()
                ->toArray();
        });
    }

    /**
     * Get current active language code (same as site language)
     */
    public static function getCurrentLanguageCode(): string
    {
        return self::getSiteLanguage();
    }

    /**
     * Get current active language record
     */
    public static function getCurrentLanguage(): ?Language
    {
        $code = self::getSiteLanguage();
        return Language::where('code', $code)->first();
    }

    /**
     * Get default language (English)
     */
    public static function getDefaultLanguage(): ?Language
    {
        return Language::where('is_default', true)->first();
    }

    /**
     * Check if a language is RTL
     */
    public static function isRTL(string $languageCode = null): bool
    {
        $code = $languageCode ?? self::getSiteLanguage();
        
        return Cache::remember("language_rtl.{$code}", self::LANGUAGES_LIST_CACHE_TTL, function () use ($code) {
            $language = Language::where('code', $code)->first();
            return $language && $language->direction === 'rtl';
        });
    }

    /**
     * Get language direction (ltr or rtl)
     */
    public static function getDirection(string $languageCode = null): string
    {
        return self::isRTL($languageCode) ? 'rtl' : 'ltr';
    }

    /**
     * Clear all translation caches
     * Called when translations are updated
     */
    public static function clearCache(): void
    {
        Cache::forget('site_language');
        Cache::forget('active_languages');
        
        // Clear translation caches for all languages
        $languages = Language::all();
        foreach ($languages as $language) {
            Cache::forget("translations.{$language->code}");
            Cache::forget("language_rtl.{$language->code}");
        }
        
        \Log::info('Translation caches cleared');
    }

    /**
     * Clear translation cache for a specific language
     * Useful when only one language file is updated
     */
    public static function clearLanguageCache(string $languageCode): void
    {
        Cache::forget("translations.{$languageCode}");
        Cache::forget("language_rtl.{$languageCode}");
        
        // If this is the current site language, also clear the site language cache
        if ($languageCode === self::getSiteLanguage()) {
            Cache::forget('site_language');
        }
        
        \Log::info("Translation cache cleared for language: {$languageCode}");
    }
}
