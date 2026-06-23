<?php

namespace App\Console\Commands;

use App\Helpers\TranslationHelper;
use Illuminate\Console\Command;

class ClearTranslationCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translation:clear {language?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear translation cache for all languages or a specific language';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $language = $this->argument('language');

        if ($language) {
            TranslationHelper::clearLanguageCache($language);
            $this->info("Translation cache cleared for language: {$language}");
        } else {
            TranslationHelper::clearCache();
            $this->info('Translation cache cleared for all languages');
        }

        return 0;
    }
}
