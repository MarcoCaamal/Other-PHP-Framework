<?php

return [
    'connection' => env('DB_CONNECTION', 'mysql'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', 3306),
    'database' => env('DB_DATABASE', 'lightweight_test'),
    'username' => env('DB_USER', 'root'),
    'password' => env('DB_PASSWORD', '')
];
