<?php

namespace LightWeight\Providers;

use LightWeight\Application;
use LightWeight\Container\Container;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Routing\Router;
use LightWeight\Server\Contracts\ServerContract;
use LightWeight\Server\PHPNativeServer;

class ServerServiceProvider extends ServiceProvider
{
    /**
     * Proporciona definiciones para el contenedor antes de su compilación
     * 
     * @return array
     */
    public function getDefinitions(): array
    {
        return [
            ServerContract::class => \DI\factory(function (\LightWeight\Config\Config $config) {
                return match($config->get('server.implementation', null)) {
                    'native' => new PHPNativeServer(),
                    default => throw new \RuntimeException("Server implementation not supported: " . $config->get('server.implementation'))
                };
            }),
            RequestContract::class => \DI\factory(function (ServerContract $server) {
                return $server->getRequest();
            }),
            Router::class => \DI\factory(function (Container $container) {
                return new Router($container);
            })
        ];
    }

    public function registerServices(Container $serviceContainer)
    {
        // Las definiciones ya están configuradas en getDefinitions()
        \LightWeight\Routing\Route::load(Application::$root . '/routes');
    }
}
