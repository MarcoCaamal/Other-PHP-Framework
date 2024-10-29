<?php

return [
    'boot' => [
        SMFramework\Providers\ServerServiceProvider::class,
        SMFramework\Providers\DatabaseDriverServiceProvider::class,
        SMFramework\Providers\SessionStorageServiceProvider::class,
        SMFramework\Providers\ViewServiceProvider::class,
    ],
    'runtime' => [
        App\Providers\RuleServiceProvider::class,
        App\Providers\RouteServiceProvider::class
    ]
];
