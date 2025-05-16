<?php

namespace App\Events\Subscribers;

use LightWeight\Events\Contracts\EventDispatcherInterface;
use LightWeight\Events\Contracts\EventInterface;
use LightWeight\Events\Contracts\EventSubscriberInterface;

/**
 * Suscriptor de eventos para gestionar eventos relacionados con usuarios
 */
class UserEventSubscriber implements EventSubscriberInterface
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
     * @param EventDispatcherInterface $dispatcher
     * @return void
     */
    public function subscribe(EventDispatcherInterface $dispatcher): void
    {
        $dispatcher->listen('user.registered', function (EventInterface $event) {
            $this->onUserRegistered($event);
        });
        
        $dispatcher->listen('user.login', function (EventInterface $event) {
            $this->onUserLogin($event);
        });
        
        $dispatcher->listen('user.logout', function (EventInterface $event) {
            $this->onUserLogout($event);
        });
    }
    
    /**
     * Manejar el evento de registro de usuario
     * 
     * @param EventInterface $event
     * @return void
     */
    public function onUserRegistered(EventInterface $event): void
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
     * @param EventInterface $event
     * @return void
     */
    public function onUserLogin(EventInterface $event): void
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
     * @param EventInterface $event
     * @return void
     */
    public function onUserLogout(EventInterface $event): void
    {
        // Implementación para el logout de usuario
        $user = $event->getData()['user'] ?? null;
        if ($user) {
            // Ejemplo: Limpiar sesiones antiguas
            // Ejemplo: Registrar hora de logout
        }
    }
}
