<?php

// config/auth.php
// Ganti SELURUH isi file ini

return [

    'defaults' => [
        'guard'     => 'marbot',
        'passwords' => 'marbots',
    ],

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'marbots',
        ],

        // Guard untuk API — dipakai oleh auth:sanctum
        'marbot' => [
            'driver'   => 'sanctum',
            'provider' => 'marbots',
        ],
    ],

    'providers' => [
        // Ganti 'users' dengan 'marbots', model Marbot
        'marbots' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Marbot::class,
        ],
    ],

    'passwords' => [
        'marbots' => [
            'provider' => 'marbots',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
