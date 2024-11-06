<?php

return [
    'connection' => env('DB_CONNECTION', 'mysql'),
    'host' => env('DB_HOST', 'localhost'),
    'port' => env('DB_PORT', 3306),
    'database' => env('DB_DATABASE', 'LightWeight'),
    'username' => env('DB_USER', 'root'),
    'password' => env('DB_PASSWORD', '')
];
