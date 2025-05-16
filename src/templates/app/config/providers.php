<?php

return [
    'boot' => [
        LightWeight\Providers\ServerServiceProvider::class,
        LightWeight\Providers\DatabaseDriverServiceProvider::class,
        LightWeight\Providers\SessionStorageServiceProvider::class,
        LightWeight\Providers\ViewServiceProvider::class,
        LightWeight\Providers\AuthenticatorServiceProvider::class,
        LightWeight\Providers\HasherServiceProvider::class,
        LightWeight\Providers\FileStorageDriverServiceProvider::class,
        LightWeight\Providers\ExceptionHandlerServiceProvider::class,
        LightWeight\Providers\EventServiceProvider::class,
        LightWeight\Providers\MailServiceProvider::class,
        App\Providers\AppEventServiceProvider::class
    ],
    'runtime' => [
        App\Providers\RuleServiceProvider::class,
        App\Providers\RouteServiceProvider::class
    ],
    'cli' => [
        LightWeight\Providers\DatabaseDriverServiceProvider::class,
    ]
];
