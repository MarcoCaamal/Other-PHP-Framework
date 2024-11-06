<?php

return [
    'boot' => [
        LightWeight\Providers\ServerServiceProvider::class,
        LightWeight\Providers\DatabaseDriverServiceProvider::class,
        LightWeight\Providers\SessionStorageServiceProvider::class,
        LightWeight\Providers\ViewServiceProvider::class,
        LightWeight\Providers\AuthenticatorServiceProvider::class,
        LightWeight\Providers\HasherServiceProvider::class
    ],
    'runtime' => [
        App\Providers\RuleServiceProvider::class,
        App\Providers\RouteServiceProvider::class
    ]
];
