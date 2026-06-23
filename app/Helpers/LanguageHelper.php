<?php

namespace App\Helpers;

use App\Models\Language;
use Illuminate\Support\Facades\File;

class LanguageHelper
{
    /**
     * Create a new language folder and translation file
     */
    public static function createLanguageFiles(Language $language): bool
    {
        try {
            $folderPath = $language->getLanguageFolderPath();

            // Create folder if it doesn't exist
            if (!File::isDirectory($folderPath)) {
                File::makeDirectory($folderPath, 0755, true);
            }

            // Get default English translations
            $defaultMessages = self::getDefaultMessages();

            // Create messages.php file with empty values (keys only)
            $content = "<?php\n\nreturn " . var_export($defaultMessages, true) . ";\n";
            File::put($language->getMessagesFilePath(), $content);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to create language files: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get default English translation keys
     */
    public static function getDefaultMessages(): array
    {
        $defaultPath = resource_path('lang/en/messages.php');

        if (File::exists($defaultPath)) {
            return require $defaultPath;
        }

        // Fallback with common translation keys
        return [
            'dashboard' => 'Dashboard',
            'users' => 'Users',
            'settings' => 'Settings',
            'logout' => 'Logout',
            'login' => 'Login',
            'welcome' => 'Welcome',
            'home' => 'Home',
            'about' => 'About',
            'contact' => 'Contact',
            'language' => 'Language',
            'profile' => 'Profile',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'save' => 'Save',
            'cancel' => 'Cancel',
            'create' => 'Create',
            'update' => 'Update',
            'search' => 'Search',
            'no_results' => 'No results found',
            'error' => 'Error',
            'success' => 'Success',
            'confirmation' => 'Confirmation',
            'are_you_sure' => 'Are you sure?',
        ];
    }

    /**
     * Get all translation keys from English messages file
     */
    public static function getAllTranslationKeys(): array
    {
        return self::getDefaultMessages();
    }

    /**
     * Get translations for a specific language
     */
    public static function getLanguageTranslations(Language $language): array
    {
        if (!$language->messagesFileExists()) {
            return [];
        }

        try {
            return require $language->getMessagesFilePath();
        } catch (\Exception $e) {
            \Log::error('Failed to load language translations: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Save translations for a language
     */
    public static function saveLanguageTranslations(Language $language, array $translations): bool
    {
        try {
            // Ensure all keys from default exist
            $defaultKeys = self::getDefaultMessages();
            $filtered = [];

            foreach ($defaultKeys as $key => $value) {
                $filtered[$key] = $translations[$key] ?? '';
            }

            $content = "<?php\n\nreturn " . var_export($filtered, true) . ";\n";
            File::put($language->getMessagesFilePath(), $content);

            // Clear translation cache for this specific language
            \App\Helpers\TranslationHelper::clearLanguageCache($language->code);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to save language translations: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete language folder and files
     */
    public static function deleteLanguageFiles(Language $language): bool
    {
        try {
            $folderPath = $language->getLanguageFolderPath();

            if (File::isDirectory($folderPath)) {
                File::deleteDirectory($folderPath);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to delete language files: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Initialize default English language
     */
    public static function initializeDefaultLanguage(): void
    {
        // Check if English language exists
        $english = Language::where('code', 'en')->first();

        if (!$english) {
            $english = Language::create([
                'name' => 'English',
                'code' => 'en',
                'direction' => 'ltr',
                'is_active' => true,
                'is_default' => true,
            ]);

            // Create language files
            self::createLanguageFiles($english);
        }
    }
}
