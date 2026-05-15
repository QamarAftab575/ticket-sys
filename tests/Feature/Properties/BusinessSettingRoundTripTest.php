<?php

namespace Tests\Feature\Properties;

use App\Models\BusinessSetting;
use Pest\Arch;

describe('Property 4: Credentials Round Trip', function () {
    it('validates that saved credentials can be retrieved unchanged', function () {
        // Generate random credential sets
        $credentialSets = collect(range(1, 100))->map(function () {
            return [
                'client_id' => 'client_' . uniqid(),
                'client_secret' => 'secret_' . uniqid(),
                'redirect_uri' => 'https://app.example.com/callback',
                'enabled' => true,
            ];
        });

        $credentialSets->each(function ($credentials) {
            // Save credentials
            BusinessSetting::set('google_oauth', $credentials);

            // Retrieve credentials
            $retrieved = BusinessSetting::get('google_oauth');

            // Verify they match
            expect($retrieved)->toBe($credentials);
        });
    })->tag('Feature: google-social-login, Property 4: Credentials Round Trip');

    it('validates that credentials are stored as JSON', function () {
        $credentials = [
            'client_id' => 'test_client_id',
            'client_secret' => 'test_secret',
            'enabled' => true,
        ];

        BusinessSetting::set('google_oauth', $credentials);

        $setting = BusinessSetting::where('key', 'google_oauth')->first();
        expect($setting->value)->toBeArray();
        expect($setting->value)->toBe($credentials);
    })->tag('Feature: google-social-login, Property 4: Credentials Round Trip');

    it('validates that default value is returned when key does not exist', function () {
        BusinessSetting::forget('nonexistent_key');

        $result = BusinessSetting::get('nonexistent_key', 'default_value');
        expect($result)->toBe('default_value');
    })->tag('Feature: google-social-login, Property 4: Credentials Round Trip');

    it('validates that forget removes the setting', function () {
        BusinessSetting::set('test_key', ['value' => 'test']);
        expect(BusinessSetting::where('key', 'test_key')->exists())->toBeTrue();

        BusinessSetting::forget('test_key');
        expect(BusinessSetting::where('key', 'test_key')->exists())->toBeFalse();
    })->tag('Feature: google-social-login, Property 4: Credentials Round Trip');
});
