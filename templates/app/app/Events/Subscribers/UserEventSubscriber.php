<?php

namespace App\Events\Subscribers;

use LightWeight\Events\Contracts\EventDispatcherContract;
use LightWeight\Events\Contracts\EventContract;
use LightWeight\Events\Contracts\EventSubscriberContract;

/**
 * Suscriptor de eventos para gestionar eventos relacionados con usuarios
 */
class UserEventSubscriber implements EventSubscriberContract
{
    /**
     * Obtiene los eventos manejados por este suscriptor
     *
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'user.registered' => 'onUserRegistered',
            'user.login' => 'onUserLogin',
            'user.logout' => 'onUserLogout'
        ];
    }

    /**
     * Registrar los listeners para el suscriptor
     *
     * @param EventDispatcherContract $dispatcher
     * @return void
     */
    public function subscribe(EventDispatcherContract $dispatcher): void
    {
        $dispatcher->listen('user.registered', function (EventContract $event) {
            $this->onUserRegistered($event);
        });

        $dispatcher->listen('user.login', function (EventContract $event) {
            $this->onUserLogin($event);
        });

        $dispatcher->listen('user.logout', function (EventContract $event) {
            $this->onUserLogout($event);
        });
    }

    /**
     * Manejar el evento de registro de usuario
     *
     * @param EventContract $event
     * @return void
     */
    public function onUserRegistered(EventContract $event): void
    {
        // Implementación para el registro de usuario
        $user = $event->getData()['user'] ?? null;
        if ($user) {
            // Ejemplo: Enviar notificación a administradores
            // Ejemplo: Actualizar estadísticas
        }
    }

    /**
     * Manejar el evento de inicio de sesión
     *
     * @param EventContract $event
     * @return void
     */
    public function onUserLogin(EventContract $event): void
    {
        // Implementación para el login de usuario
        $user = $event->getData()['user'] ?? null;
        if ($user) {
            // Ejemplo: Registrar actividad
            // Ejemplo: Detectar inicio de sesión sospechoso
        }
    }

    /**
     * Manejar el evento de cierre de sesión
     *
     * @param EventContract $event
     * @return void
     */
    public function onUserLogout(EventContract $event): void
    {
        // Implementación para el logout de usuario
        $user = $event->getData()['user'] ?? null;
        if ($user) {
            // Ejemplo: Limpiar sesiones antiguas
            // Ejemplo: Registrar hora de logout
        }
    }
}
