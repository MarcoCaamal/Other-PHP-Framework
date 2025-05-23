<?php

/**
 * Configuración de correo electrónico
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Driver predeterminado
    |--------------------------------------------------------------------------
    |
    | Este es el driver que se utilizará por defecto para enviar correos.
    | Opciones disponibles: "phpmailer", "log", etc.
    |
    */
    'default' => env('MAIL_DRIVER', 'phpmailer'),

    /*
    |--------------------------------------------------------------------------
    | Configuración del servidor SMTP
    |--------------------------------------------------------------------------
    |
    | Aquí puedes configurar todos los ajustes necesarios para el servidor SMTP
    | que se utilizará para enviar correos electrónicos desde tu aplicación.
    |
    */
    'host' => env('MAIL_HOST', 'smtp.example.com'),
    'port' => env('MAIL_PORT', 587),
    'encryption' => env('MAIL_ENCRYPTION', 'ENCRYPTION_STARTTLS'), // ENCRYPTION_SMTPS o ENCRYPTION_STARTTLS
    'username' => env('MAIL_USERNAME', ''),
    'password' => env('MAIL_PASSWORD', ''),
    'auth' => env('MAIL_AUTH', true),

    /*
    |--------------------------------------------------------------------------
    | Configuración global del remitente
    |--------------------------------------------------------------------------
    |
    | Puedes configurar la dirección de correo electrónico y el nombre que se
    | utilizará como remitente predeterminado para todos los correos enviados.
    |
    */
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Opciones de depuración y registro
    |--------------------------------------------------------------------------
    */
    'debug' => env('MAIL_DEBUG', 0),
    'log_channel' => env('MAIL_LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Configuración de plantillas
    |--------------------------------------------------------------------------
    */
    'templates_path' => '/resources/views/emails',
    'charset' => 'UTF-8',
];
