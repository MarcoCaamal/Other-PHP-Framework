<?php

return [
    'implementation' => 'native',

    /*
    |--------------------------------------------------------------------------
    | Force HTTPS
    |--------------------------------------------------------------------------
    |
    | If set to true, all HTTP requests will be redirected to HTTPS.
    | This is useful for production environments where secure connections
    | are required.
    |
    */
    'force_https' => env('SERVER_FORCE_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | Force WWW
    |--------------------------------------------------------------------------
    |
    | If set to true, requests without 'www' prefix will be redirected to
    | the same URL with 'www' prefix. If force_https is also true, the
    | redirect will go to https://www.
    |
    */
    'force_www' => env('SERVER_FORCE_WWW', false)
];
