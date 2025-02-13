<?php

return [

    // Application name
    'name'      => env('APP_NAME', 'DevAmirix'),

    // Debug mode
    'debug'     => (bool) env('APP_DEBUG', false),

    // Application URL
    'url'       => env('APP_URL'),

    // Timezone
    'timezone'  => env('APP_TIMEZONE', 'UTC'),

    // Locale
    'locale'    => env('APP_LOCALE', 'en'),

    // Encryption cipher and key
    'cipher'    => 'chacha20',
    'key'       => env('APP_KEY'),

    // Error pages
    'errors' => [
        '401'   => '',
        '402'   => '',
        '403'   => 'components/panel/errors/403',
        '404'   => 'components/panel/errors/404',
        '405'   => 'components/panel/errors/405',
        '419'   => '',
        '429'   => '',
        '500'   => 'components/panel/errors/500',
        '503'   => '',
    ],

    // Minify all project files
    'minify' => [
        'html'    => true,

        'css'   => true,
        'js'    => true,
    ]
];