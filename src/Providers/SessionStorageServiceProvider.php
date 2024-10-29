<?php

namespace SMFramework\Providers;

use SMFramework\Providers\Contracts\ServiceProviderContract;
use SMFramework\Session\Contracts\SessionStorageContract;
use SMFramework\Session\PhpNativeSessionStorage;

class SessionStorageServiceProvider implements ServiceProviderContract
{
    public function registerServices()
    {
        match (config("session.storage", "native")) {
            "native" => singleton(SessionStorageContract::class, PhpNativeSessionStorage::class),
        };
    }
}
