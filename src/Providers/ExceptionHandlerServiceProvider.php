<?php

namespace LightWeight\Providers;

use LightWeight\Exceptions\Contracts\ExceptionHandlerContract;
use Psr\Container\ContainerInterface;

/**
 * Service provider for exception handling
 */
class ExceptionHandlerServiceProvider
{
    /**
     * Register the exception handler service
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function registerServices(ContainerInterface $container): void
    {
        // Register exception handler binding
        $handlerClass = config('app.exception_handler', \App\Exceptions\Handler::class);
        $container->set(ExceptionHandlerContract::class, \DI\create($handlerClass));
    }
}
