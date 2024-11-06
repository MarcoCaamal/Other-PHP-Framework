<?php

namespace LightWeight\Providers;

use LightWeight\Crypto\Bcrypt;
use LightWeight\Crypto\Contracts\HasherContract;
use LightWeight\Providers\Contracts\ServiceProviderContract;

class HasherServiceProvider implements ServiceProviderContract
{
    public function registerServices(\DI\Container $serviceContainer)
    {
        match(config('hashing.hasher', 'bcrypt')) {
            'bcrypt' => $serviceContainer->set(HasherContract::class, \DI\create(Bcrypt::class)),
        };
    }
}
