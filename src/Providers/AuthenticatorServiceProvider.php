<?php

namespace SMFramework\Providers;

use SMFramework\Auth\Authenticators\SessionAuthenticator;
use SMFramework\Auth\Contracts\Authenticators\AuthenticatorContract;
use SMFramework\Providers\Contracts\ServiceProviderContract;

class AuthenticatorServiceProvider implements ServiceProviderContract
{
    public function registerServices()
    {
        match (config("auth.method", "session")) {
            "session" => singleton(AuthenticatorContract::class, SessionAuthenticator::class),
        };
    }
}
