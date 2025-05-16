<?php

namespace App\Providers;

use DI\Container;
use LightWeight\App;
use LightWeight\Providers\Contracts\ServiceProviderContract;
use LightWeight\Routing\Route;
use LightWeight\Routing\Router;

class RouteServiceProvider implements ServiceProviderContract
{
    /**
     * @inheritDoc
     */
    public function registerServices(Container $serviceContainer)
    {
        $router = app(Router::class);
        $router->setGlobalMiddlewares([
            // Descomentar y ajustar según tus necesidades
            // \App\Middleware\CorsMiddleware::class,
            // \App\Middleware\RequestLogMiddleware::class,
        ]);
        
        // Configurar grupos de middleware
        $router->setMiddlewareGroups([
            'web' => [
                // \App\Middleware\SessionMiddleware::class,
                // \App\Middleware\CsrfMiddleware::class,
            ],
            'api' => [
                // \App\Middleware\JsonResponseMiddleware::class,
                // \App\Middleware\ThrottleRequestsMiddleware::class,
            ],
            'auth' => [
                // \App\Middleware\AuthMiddleware::class,
            ]
        ]);
        
        Route::load(App::$root . '/routes');
    }
}
