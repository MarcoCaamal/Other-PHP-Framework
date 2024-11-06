<?php

namespace LightWeight\Providers;

use DI\Container as DIContainer;
use LightWeight\Providers\Contracts\ServiceProviderContract;
use LightWeight\Server\Contracts\ServerContract;
use LightWeight\Server\PHPNativeServer;

class ServerServiceProvider implements ServiceProviderContract
{
    public function registerServices(DIContainer $serviceContainer)
    {
        match(config('server.implementation', 'native')) {
            'native' => $serviceContainer->set(ServerContract::class, \DI\create(PHPNativeServer::class))
        };
    }
}
