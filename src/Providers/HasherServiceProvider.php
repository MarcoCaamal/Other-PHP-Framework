<?php

namespace LightWeight\Providers;

use LightWeight\Crypto\Bcrypt;
use LightWeight\Crypto\Contracts\HasherContract;
use LightWeight\Providers\Contracts\ServiceProviderContract;

class HasherServiceProvider implements ServiceProviderContract
{
    public function registerServices()
    {
        match (config("hashing.hasher", "bcrypt")) {
            "bcrypt" => singleton(HasherContract::class, Bcrypt::class),
        };
    }
}
