<?php

namespace LightWeight\Providers;

use DI\Container as DIContainer;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Providers\Contracts\ServiceProviderContract;
use LightWeight\Routing\Router;
use LightWeight\Server\Contracts\ServerContract;
use LightWeight\Server\PHPNativeServer;

class ServerServiceProvider implements ServiceProviderContract
{
    public function registerServices(DIContainer $serviceContainer)
    {
        match(config('server.implementation', 'native')) {
            'native' => $serviceContainer->set(ServerContract::class, \DI\create(PHPNativeServer::class))
        };
        $serviceContainer->set(RequestContract::class, fn(ServerContract $server) => $server->getRequest());
        $serviceContainer->set(Router::class, \DI\create(Router::class));

    }
}
