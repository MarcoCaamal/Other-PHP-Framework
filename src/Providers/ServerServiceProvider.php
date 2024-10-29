<?php

namespace SMFramework\Providers;

use SMFramework\Providers\Contracts\ServiceProviderContract;
use SMFramework\Server\Contracts\ServerContract;
use SMFramework\Server\PHPNativeServer;

class ServerServiceProvider implements ServiceProviderContract
{
    public function registerServices()
    {
        singleton(ServerContract::class, PHPNativeServer::class);
    }
}
