<?php

// config/auth.php
// Ganti SELURUH isi file ini

return [

    'defaults' => [
        'guard'     => 'web',
        'passwords' => 'marbots',
    ],

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'marbots',
        ],

        'bendahara' => [
            'driver'   => 'session',
            'provider' => 'bendaharas',
        ],
        'marbot' => [
            'driver'   => 'session',
            'provider' => 'marbots',
        ],
    ],

    'providers' => [
        // Ganti 'users' dengan 'marbots', model Marbot
        'marbots' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Marbot::class,
        ],
        'bendaharas' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Bendahara::class,
        ],
    ],

    'passwords' => [
        'marbots' => [
            'provider' => 'marbots',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],
        'bendaharas' => [
            'provider' => 'bendaharas',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
