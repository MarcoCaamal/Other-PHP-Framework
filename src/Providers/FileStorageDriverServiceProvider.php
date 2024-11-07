<?php

namespace LightWeight\Providers;

use LightWeight\Providers\Contracts\ServiceProviderContract;
use LightWeight\Storage\Drivers\Contracts\FileStorageDriverContract;
use LightWeight\Storage\Drivers\DiskFileStorage;

class FileStorageDriverServiceProvider implements ServiceProviderContract
{
    /**
     * @inheritDoc
     */
    public function registerServices(\DI\Container $serviceContainer)
    {
        match (config("storage.driver", "disk")) {
            "disk" => $serviceContainer->set(FileStorageDriverContract::class, \DI\create(DiskFileStorage::class)->constructor(config('storage.path', '/storage')))
        };
    }
}
