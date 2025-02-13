<?php

use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;

return [

    // Session configurations
    'session' => [
        'save_handler'      => env('SESSION_DRIVER', 'files'),
        'save_path'         => env('SESSION_SAVE_PATH', '/tmp/sessions'),
        'name'              => env('SESSION_NAME', 'DevAmirix'),
        'cookie_lifetime'   => env('SESSION_LIFETIME', 43200),
        'gc_maxlifetime'    => env('SESSION_LIFETIME', 43200),
        'cookie_path'       => env('SESSION_PATH', '/'),
        'cookie_domain'     => env('SESSION_DOMAIN', null),
        'cookie_secure'     => env('SESSION_SECURE', false),
        'cookie_httponly'   => env('SESSION_HTTPONLY', true),
    ],

    // Session handler
    'handler'               => new NativeFileSessionHandler(),

    // Metadata bag
    'metadataBag'           => null,
];