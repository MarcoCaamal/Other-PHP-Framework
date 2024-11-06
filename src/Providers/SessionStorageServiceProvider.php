<?php

namespace LightWeight\Providers;

use DI\Container as DIContainer;
use LightWeight\Providers\Contracts\ServiceProviderContract;
use LightWeight\Session\Contracts\SessionStorageContract;
use LightWeight\Session\PhpNativeSessionStorage;

class SessionStorageServiceProvider implements ServiceProviderContract
{
    public function registerServices(DIContainer $serviceContainer)
    {
        match(config('session.storage', 'native')) {
            'native' => $serviceContainer->set(SessionStorageContract::class, \DI\create(PhpNativeSessionStorage::class))
        };
    }
}
