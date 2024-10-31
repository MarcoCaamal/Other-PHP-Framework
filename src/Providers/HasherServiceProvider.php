<?php

namespace SMFramework\Providers;

use SMFramework\Crypto\Bcrypt;
use SMFramework\Crypto\Contracts\HasherContract;
use SMFramework\Providers\Contracts\ServiceProviderContract;

class HasherServiceProvider implements ServiceProviderContract
{
    public function registerServices()
    {
        match (config("hashing.hasher", "bcrypt")) {
            "bcrypt" => singleton(HasherContract::class, Bcrypt::class),
        };
    }
}
