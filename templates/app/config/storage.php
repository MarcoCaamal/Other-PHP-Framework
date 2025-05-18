<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Storage Driver
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default storage driver that should be used
    | by the framework.
    |
    */
    'default' => env('FILE_STORAGE', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Storage Drivers Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many storage "drivers" as you wish. You may
    | even configure multiple drivers of the same type with different names.
    | 
    | Supported drivers: "local", "public", "s3", "ftp"
    |
    */
    'drivers' => [
        'local' => [
            'driver' => 'local',
            'path' => env('LOCAL_STORAGE_PATH', storagePath('app/private')),
            'visibility' => 'private',
        ],
        
        'public' => [
            'driver' => 'public',
            'path' => env('PUBLIC_STORAGE_PATH', storagePath('app/public')),
            'url' => env('APP_URL', 'http://localhost'),
            'storage_uri' => env('PUBLIC_STORAGE_URI', 'uploads'),
            'visibility' => 'public',
        ],
        
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'visibility' => 'public',
        ],
        
        'ftp' => [
            'driver' => 'ftp',
            'host' => env('FTP_HOST'),
            'username' => env('FTP_USERNAME'),
            'password' => env('FTP_PASSWORD'),
            'port' => env('FTP_PORT', 21),
            'root' => env('FTP_ROOT', '/'),
            'passive' => env('FTP_PASSIVE', true),
            'ssl' => env('FTP_SSL', false),
            'timeout' => env('FTP_TIMEOUT', 30),
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Directory
    |--------------------------------------------------------------------------
    |
    | This is the default local path where files will be stored. This path
    | can be used by drivers if they need a local cache.
    |
    */
    'path' => storagePath(),
    
    /*
    |--------------------------------------------------------------------------
    | Public URL and URI for Storage
    |--------------------------------------------------------------------------
    |
    | The application URL is used to generate public URLs to files stored
    | in the storage directory. The storage URI is the path segment that
    | comes after the application URL.
    |
    */
    'url' => env('APP_URL', 'http://localhost'),
    'storage_uri' => env('STORAGE_URI', 'uploads')
];
