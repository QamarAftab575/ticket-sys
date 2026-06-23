<?php

namespace Database\Seeders;

use App\Helpers\LanguageHelper;
use App\Models\BusinessSetting;
use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Initialize default English language
        LanguageHelper::initializeDefaultLanguage();
        
        // Set default selected language in business_settings
        BusinessSetting::set('default_selected_language', 'en');
    }
}
