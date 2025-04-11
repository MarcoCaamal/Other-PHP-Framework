<?php

return [
    'boot' => [
        LightWeight\Providers\ServerServiceProvider::class,
        LightWeight\Providers\DatabaseDriverServiceProvider::class,
        LightWeight\Providers\SessionStorageServiceProvider::class,
        LightWeight\Providers\ViewServiceProvider::class,
        LightWeight\Providers\AuthenticatorServiceProvider::class,
        LightWeight\Providers\HasherServiceProvider::class,
        LightWeight\Providers\FileStorageDriverServiceProvider::class
    ],
    'runtime' => [
        App\Providers\RuleServiceProvider::class,
        App\Providers\RouteServiceProvider::class
    ],
    'cli' => [
        LightWeight\Providers\DatabaseDriverServiceProvider::class,
    ]
];
