<?php

namespace LightWeight\Providers;

use DI\Container as DIContainer;
use LightWeight\Auth\Authenticators\SessionAuthenticator;
use LightWeight\Auth\Contracts\Authenticators\AuthenticatorContract;
use LightWeight\Providers\Contracts\ServiceProviderContract;
use Src\Auth\JWT\Contracts\JWTServiceContract;
use Src\Auth\JWT\JWTService;

class AuthenticatorServiceProvider implements ServiceProviderContract
{
    public function registerServices(DIContainer $serviceContainer)
    {
        match(config('auth.method', 'session')) {
            'session' => $serviceContainer->set(AuthenticatorContract::class, \DI\create(SessionAuthenticator::class))
        };
        $serviceContainer->set(
            JWTServiceContract::class, 
            \DI\create(JWTService::class)
                ->constructor(
                    env('APP_KEY', 'default_key'),
                    config('auth.jwt_options.digest_alg', 'HS256'),
                    config('auth.jwt_options.private_key_bits', 1024),
                    config('auth.jwt_options.max_age', 3600),
                    config('auth.jwt_options.leeway', 60),
                )
        );
    }
}
