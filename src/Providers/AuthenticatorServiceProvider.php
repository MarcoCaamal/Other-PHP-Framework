<?php

namespace LightWeight\Providers;

use LightWeight\Auth\Authenticators\SessionAuthenticator;
use LightWeight\Auth\Contracts\Authenticators\AuthenticatorContract;
use LightWeight\Auth\JWT\Contracts\JWTServiceContract;
use LightWeight\Auth\JWT\JWTService;
use LightWeight\Container\Container;

class AuthenticatorServiceProvider extends ServiceProvider
{
    /**
     * Proporciona definiciones para el contenedor antes de su compilación
     *
     * @return array
     */
    public function getDefinitions(): array
    {
        return [
            // Autenticador principal
            AuthenticatorContract::class => \DI\factory(function (\LightWeight\Config\Config $config) {
                return match($config->get('auth.method', 'session')) {
                    'session' => new SessionAuthenticator(),
                    default => throw new \RuntimeException("Authentication method not supported: " . $config->get('auth.method'))
                };
            }),

            // Servicio JWT
            JWTServiceContract::class => \DI\factory(function (\LightWeight\Config\Config $config) {
                return new JWTService(
                    $config->get('auth.jwt_options.secret'),
                    $config->get('auth.jwt_options.digest_alg', 'HS256'),
                    $config->get('auth.jwt_options.max_age', 3600),
                    $config->get('auth.jwt_options.leeway', 'LightWeight'),
                );
            }),
        ];
    }

    public function registerServices(Container $serviceContainer)
    {
        // Las definiciones ya están configuradas en getDefinitions()
    }
}
