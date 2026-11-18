<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supported application locales
    |--------------------------------------------------------------------------
    |
    | The array keys are Laravel locale identifiers.
    | The html_locale values use standard BCP 47 language tags.
    |
    */

    'supported' => [

        'en' => [
            'name' => 'English',
            'native_name' => 'English',
            'html_locale' => 'en',
        ],

        'es_419' => [
            'name' => 'Spanish (Latin America)',
            'native_name' => 'Español (Latinoamérica)',
            'html_locale' => 'es-419',
        ],

        'pt_BR' => [
            'name' => 'Portuguese (Brazil)',
            'native_name' => 'Português (Brasil)',
            'html_locale' => 'pt-BR',
        ],

        'pt_PT' => [
            'name' => 'Portuguese (Portugal)',
            'native_name' => 'Português (Portugal)',
            'html_locale' => 'pt-PT',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Default and fallback locales
    |--------------------------------------------------------------------------
    */

    'default' => 'en',

    'fallback' => 'en',

];
