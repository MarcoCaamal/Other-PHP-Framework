<?php

return [
    'boot' => [
        // Servicios fundamentales del sistema
        LightWeight\Providers\ServerServiceProvider::class,
        LightWeight\Providers\ExceptionHandlerServiceProvider::class,
        LightWeight\Providers\LogServiceProvider::class,
        LightWeight\Providers\EventServiceProvider::class,
        LightWeight\Providers\DatabaseDriverServiceProvider::class,
    ],
    'runtime' => [
        // Servicios web y procesamiento de solicitudes
        LightWeight\Providers\SessionStorageServiceProvider::class,
        LightWeight\Providers\ViewServiceProvider::class,
        LightWeight\Providers\AuthenticatorServiceProvider::class,
        LightWeight\Providers\HasherServiceProvider::class,
        LightWeight\Providers\FileStorageDriverServiceProvider::class,
        LightWeight\Providers\MailServiceProvider::class,

        // Servicios de eventos de aplicación
        App\Providers\AppEventServiceProvider::class,

        // Servicios de aplicación
        App\Providers\RuleServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ],
    'cli' => [
        LightWeight\Providers\DatabaseDriverServiceProvider::class,
    ]
];
