<?php

test('all supported locales have the same common translation keys', function () {
    $supportedLocales = array_keys(
        config('openstock.locales.supported', [])
    );

    $referenceLocale = config('openstock.locales.default', 'en');

    $referenceTranslations = require lang_path(
        "{$referenceLocale}/common.php"
    );

    $referenceKeys = array_keys($referenceTranslations);

    foreach ($supportedLocales as $locale) {
        $translations = require lang_path(
            "{$locale}/common.php"
        );

        $localeKeys = array_keys($translations);

        $missingKeys = array_diff($referenceKeys, $localeKeys);
        $unexpectedKeys = array_diff($localeKeys, $referenceKeys);

        expect($missingKeys)->toBe(
            [],
            "Locale [{$locale}] is missing translation keys."
        );

        expect($unexpectedKeys)->toBe(
            [],
            "Locale [{$locale}] has unexpected translation keys."
        );
    }
});
