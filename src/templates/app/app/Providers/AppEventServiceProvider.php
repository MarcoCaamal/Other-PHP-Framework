<?php

namespace App\Providers;

use LightWeight\Events\Contracts\EventDispatcherInterface;
use LightWeight\Providers\EventServiceProvider as BaseEventServiceProvider;

/**
 * Proveedor de servicios para el sistema de eventos específico de la aplicación
 */
class AppEventServiceProvider extends BaseEventServiceProvider
{
    /**
     * Lista de listeners a registrar para cada evento
     * 
     * @var array<string, array<class-string|\Closure>>
     */
    protected array $listen = [
        'user.registered' => [
            \App\Events\Listeners\SendWelcomeEmailListener::class,
        ],
        'user.login' => [
            function ($event) {
                // Lógica para manejar el inicio de sesión
                $user = $event->getData()['user'] ?? null;
                if ($user) {
                    // Ejemplo: Actualizar fecha de último login
                    // $user->updateLastLogin();
                }
            },
        ],
        'application.bootstrapped' => [
            function ($event) {
                // Lógica para ejecutar cuando la aplicación ha iniciado
            }
        ],
    ];
}
