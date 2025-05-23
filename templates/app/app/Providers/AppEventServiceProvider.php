<?php

namespace App\Providers;

use LightWeight\Events\Contracts\EventDispatcherContract;

/**
 * Proveedor de servicios para el sistema de eventos específico de la aplicación
 */
class AppEventServiceProvider extends \LightWeight\Providers\ServiceProvider
{
    /**
     * Lista de listeners a registrar para cada evento
     * 
     * @var array<string, array<class-string>>
     */
    protected array $listen = [
        'user.registered' => [
            \App\Events\Listeners\SendWelcomeEmailListener::class,
        ],
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function registerServices($container)
    {
        
        // Registramos los listeners que usan closures
        $dispatcher = $container->get(EventDispatcherContract::class);
        
        $dispatcher->listen('user.login', function ($event) {
            // Lógica para manejar el inicio de sesión
            $user = $event->getData()['user'] ?? null;
            if ($user) {
                // Ejemplo: Actualizar fecha de último login
                // $user->updateLastLogin();
            }
        });
        
        $dispatcher->listen('application.bootstrapped', function ($event) {
            // Lógica para ejecutar cuando la aplicación ha iniciado
        });
    }
}
