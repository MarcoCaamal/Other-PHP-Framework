<?php

namespace LightWeight\Providers;

use LightWeight\Auth\Authenticators\SessionAuthenticator;
use LightWeight\Auth\Contracts\Authenticators\AuthenticatorContract;
use LightWeight\Providers\Contracts\ServiceProviderContract;

class AuthenticatorServiceProvider implements ServiceProviderContract
{
    public function registerServices()
    {
        match (config("auth.method", "session")) {
            "session" => singleton(AuthenticatorContract::class, SessionAuthenticator::class),
        };
    }
}
