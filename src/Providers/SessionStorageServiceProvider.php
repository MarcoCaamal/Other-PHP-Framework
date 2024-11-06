<?php

namespace LightWeight\Providers;

use LightWeight\Providers\Contracts\ServiceProviderContract;
use LightWeight\Session\Contracts\SessionStorageContract;
use LightWeight\Session\PhpNativeSessionStorage;

class SessionStorageServiceProvider implements ServiceProviderContract
{
    public function registerServices()
    {
        match (config("session.storage", "native")) {
            "native" => singleton(SessionStorageContract::class, PhpNativeSessionStorage::class),
        };
    }
}
