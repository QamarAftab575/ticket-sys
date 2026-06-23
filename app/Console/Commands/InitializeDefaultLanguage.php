<?php

namespace App\Console\Commands;

use App\Helpers\LanguageHelper;
use Illuminate\Console\Command;

class InitializeDefaultLanguage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:initialize-default-language';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize the default English language';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Initializing default English language...');

        try {
            LanguageHelper::initializeDefaultLanguage();
            $this->info('✓ Default English language initialized successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('✗ Failed to initialize default language: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
