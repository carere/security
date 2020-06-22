<?php

return [
    'env' => env('APP_ENV', 'local'),
    'database' => [
        'driver' => env('DB_CONNECTION', 'pdo_pgsql'),
        'user' => env('DB_USERNAME', 'carere'),
        'password' => env('DB_PASSWORD', 'password'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '5432'),
        'dbname' => env('DB_DATABASE', 'security'),
    ],
];
