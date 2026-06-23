<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\LanguageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveTranslationsRequest;
use App\Http\Requests\StoreLanguageRequest;
use App\Http\Requests\UpdateLanguageRequest;
use App\Models\Language;
use Illuminate\Http\Request;

/**
 * AdminLanguageController
 *
 * Complete Language Management System for the Admin Panel
 * Enables admins to create, manage, and maintain multilingual support without code changes
 *
 * WORKFLOW:
 * 1. Admin views all languages: GET /admin/languages
 * 2. Admin creates new language: POST /admin/languages
 *    - System creates folder: resources/lang/{code}/
 *    - System creates file: resources/lang/{code}/messages.php
 *    - Copies keys from English with empty values
 * 3. Admin manages translations: GET /admin/languages/{language}/translations
 * 4. Admin saves translations: POST /admin/languages/{language}/translations
 * 5. Admin toggles language status (Active/Inactive): POST /admin/languages/{language}/toggle
 * 6. Admin deletes language (except English): DELETE /admin/languages/{language}
 *
 * FEATURES:
 * - English is always the default and cannot be deleted
 * - Supports LTR (Left-to-Right) and RTL (Right-to-Left) languages
 * - Automatic translation file generation
 * - Translation key management with English as source of truth
 * - Language activation/deactivation
 * - Full CRUD operations on languages
 *
 * DATABASE:
 * - Table: languages
 * - Columns: id, name, code (unique), direction, is_active, is_default, timestamps
 * - Example: id=1, name="Arabic", code="ar", direction="rtl", is_active=1, is_default=0
 */
class AdminLanguageController extends Controller
{
    /**
     * Display a listing of all languages.
     * Shows table with: Name, Code, Direction, Default status, Status (Active/Inactive)
     */
    public function index()
    {
        $languages = Language::orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return inertia('Admin/Languages/Index', [
            'languages' => $languages,
            'siteLanguage' => \App\Helpers\TranslationHelper::getSiteLanguage(),
        ]);
    }

    /**
     * Set the site-wide default language.
     * This language will be used across the entire application.
     * Cache duration: 30 days (cleared only when admin changes language)
     */
    public function setSiteLanguage(Request $request)
    {
        $request->validate([
            'language_code' => 'required|string|exists:languages,code',
        ]);

        $language = Language::where('code', $request->language_code)
            ->where('is_active', true)
            ->first();

        if (!$language) {
            return back()->withErrors(['error' => 'Selected language is not active.']);
        }

        // Set site-wide language with long-term cache (30 days)
        \App\Helpers\TranslationHelper::setSiteLanguage($language->code);

        return back()->with('success', "Site language changed to {$language->name}!");
    }

    /**
     * Show the form for creating a new language.
     * Fields: Language Name, Language Code, Direction (LTR/RTL)
     */
    public function create()
    {
        return inertia('Admin/Languages/Create');
    }

    /**
     * Store a newly created language in storage.
     * Creates:
     * - Database record in languages table (INACTIVE by default)
     * - Folder: resources/lang/{code}/
     * - File: resources/lang/{code}/messages.php with English keys (empty values)
     */
    public function store(StoreLanguageRequest $request)
    {
        // Prevent creating English if it doesn't exist via this form
        if ($request->code === 'en' && !Language::where('code', 'en')->exists()) {
            return back()->withErrors(['code' => 'English language must be initialized by the system.']);
        }

        // Create the language (INACTIVE by default until admin activates it)
        $language = Language::create([
            'name' => $request->name,
            'code' => $request->code,
            'direction' => 'ltr',
            'is_active' => false, // INACTIVE by default
            'is_default' => false,
        ]);

        // Create language folder and translation files
        if (LanguageHelper::createLanguageFiles($language)) {
            return redirect()->route('admin.languages.index')
                ->with('success', "Language '{$language->name}' created successfully! Activate it to use as site language.");
        }

        // If file creation failed, delete the language record
        $language->delete();
        return back()->withErrors(['error' => 'Failed to create language files. Please try again.']);
    }

    /**
     * Show the form for editing the specified language.
     * Can only edit name and direction (code is immutable)
     */
    public function edit(Language $language)
    {
        // Prevent editing if English doesn't exist
        if ($language->code === 'en') {
            return redirect()->route('admin.languages.index')
                ->withErrors(['error' => 'Cannot edit the default English language.']);
        }

        return inertia('Admin/Languages/Edit', [
            'language' => $language,
        ]);
    }

    /**
     * Update the specified language in storage.
     * Only updates: name, direction (code is immutable)
     */
    public function update(UpdateLanguageRequest $request, Language $language)
    {
        // Prevent updating English default language
        if ($language->is_default) {
            return back()->withErrors(['error' => 'Cannot edit the default English language.']);
        }

        $language->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.languages.index')
            ->with('success', "Language '{$language->name}' updated successfully!");
    }

    /**
     * Delete the specified language.
     * - Removes folder: resources/lang/{code}/
     * - Removes database record
     * - Cannot delete English (is_default = 1)
     */
    public function destroy(Language $language)
    {
        // Prevent deleting the default English language
        if ($language->is_default) {
            return back()->withErrors(['error' => 'Default English language cannot be deleted.']);
        }

        // Delete language files
        LanguageHelper::deleteLanguageFiles($language);

        // Delete language record
        $language->delete();

        return redirect()->route('admin.languages.index')
            ->with('success', "Language '{$language->name}' deleted successfully!");
    }

    /**
     * Toggle language active/inactive status.
     * - Active languages appear in frontend language selector
     * - Inactive languages are hidden from users
     * - English must always remain active
     */
    public function toggle(Language $language)
    {
        // Prevent deactivating the default English language
        if ($language->is_default && $language->is_active) {
            return back()->withErrors(['error' => 'Default English language must remain active.']);
        }

        $language->update(['is_active' => !$language->is_active]);

        $status = $language->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Language '{$language->name}' has been {$status}!");
    }

    /**
     * Show translations management page for a language.
     * Displays table with:
     * - Key (from English)
     * - English Value (source of truth)
     * - Translation Value (editable textarea for target language)
     *
     * Features:
     * - Search/filter by key or value
     * - Progress indicator (completed translations %)
     * - All English keys shown with empty values initially
     */
    public function manageTranslations(Language $language)
    {
        // Get all translation keys from English (default)
        $translationKeys = LanguageHelper::getAllTranslationKeys();

        // Get current translations for this language
        $currentTranslations = LanguageHelper::getLanguageTranslations($language);

        // Map keys with English and current values
        $translations = [];
        foreach ($translationKeys as $key => $englishValue) {
            $translations[] = [
                'key' => $key,
                'english_value' => $englishValue,
                'value' => $currentTranslations[$key] ?? '',
            ];
        }

        return inertia('Admin/Languages/ManageTranslations', [
            'language' => $language,
            'translations' => $translations,
        ]);
    }

    /**
     * Save translations for a language.
     * - Updates: resources/lang/{code}/messages.php
     * - Only saves keys that exist in English (source of truth)
     * - Preserves all English keys in the output file
     *
     * @param SaveTranslationsRequest $request Contains: translations[key => value]
     * @param Language $language The target language to save translations for
     */
    public function saveTranslations(SaveTranslationsRequest $request, Language $language)
    {
        if (LanguageHelper::saveLanguageTranslations($language, $request->translations)) {
            return back()->with('success', 'Translations saved successfully!');
        }

        return back()->withErrors(['error' => 'Failed to save translations. Please try again.']);
    }
}
