<?php

namespace App\Providers;

use LightWeight\Container\Container;
use LightWeight\Application;
use LightWeight\Routing\Route;
use LightWeight\Routing\Router;

class RouteServiceProvider extends \LightWeight\Providers\ServiceProvider
{
    /**
     * @inheritDoc
     */
    public function registerServices(Container $serviceContainer)
    {
        $router = singleton(Router::class);
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
        
        Route::load(Application::$root . '/routes');
    }
}
