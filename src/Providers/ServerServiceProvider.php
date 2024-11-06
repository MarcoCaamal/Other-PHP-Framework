<?php

namespace LightWeight\Providers;

use LightWeight\Providers\Contracts\ServiceProviderContract;
use LightWeight\Server\Contracts\ServerContract;
use LightWeight\Server\PHPNativeServer;

class ServerServiceProvider implements ServiceProviderContract
{
    public function registerServices()
    {
        singleton(ServerContract::class, PHPNativeServer::class);
    }
}
