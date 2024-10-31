<?php

return [
    'boot' => [
        SMFramework\Providers\ServerServiceProvider::class,
        SMFramework\Providers\DatabaseDriverServiceProvider::class,
        SMFramework\Providers\SessionStorageServiceProvider::class,
        SMFramework\Providers\ViewServiceProvider::class,
        SMFramework\Providers\AuthenticatorServiceProvider::class,
        SMFramework\Providers\HasherServiceProvider::class
    ],
    'runtime' => [
        App\Providers\RuleServiceProvider::class,
        App\Providers\RouteServiceProvider::class
    ]
];
