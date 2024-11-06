<?php

namespace LightWeight\Providers;

use DI\Container as DIContainer;
use LightWeight\Auth\Authenticators\SessionAuthenticator;
use LightWeight\Auth\Contracts\Authenticators\AuthenticatorContract;
use LightWeight\Providers\Contracts\ServiceProviderContract;

class AuthenticatorServiceProvider implements ServiceProviderContract
{
    public function registerServices(DIContainer $serviceContainer)
    {
        match(config('auth.method', 'session')) {
            'session' => $serviceContainer->set(AuthenticatorContract::class, \DI\create(SessionAuthenticator::class))
        };
    }
}
