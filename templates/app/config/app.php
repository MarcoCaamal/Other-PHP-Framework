<?php

return [
    'name' => env('APP_NAME', 'LightWeight'),
    'env' => env('APP_ENV', 'development'),
    'url' => env('APP_URL', 'localhost'),
    'debug' => env('APP_DEBUG', false),
    
    /**
     * Application exception handler
     */
    'exception_handler' => \App\Exceptions\Handler::class,
];
