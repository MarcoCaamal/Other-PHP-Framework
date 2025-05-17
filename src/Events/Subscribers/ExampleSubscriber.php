<?php

namespace LightWeight\Events\Subscribers;

use LightWeight\Events\Contracts\EventDispatcherContract;
use LightWeight\Events\Contracts\EventContract;
use LightWeight\Events\Contracts\EventSubscriberContract;

/**
 * Ejemplo de suscriptor de eventos
 * 
 * Esta clase proporciona un ejemplo de cómo crear un suscriptor de eventos
 * que agrupa varios oyentes (listeners) relacionados.
 */
class ExampleSubscriber implements EventSubscriberContract
{
    /**
     * Obtiene los eventos manejados por este suscriptor
     * 
     * @return array<string, string> Mapa de eventos a métodos del manejador
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'application.bootstrapped' => 'onApplicationBootstrapped',
            'application.terminating' => 'onApplicationTerminating'
        ];
    }
    
    /**
     * Registra los oyentes para este suscriptor
     *
     * @param EventDispatcherContract $dispatcher
     * @return void
     */
    public function subscribe(EventDispatcherContract $dispatcher): void
    {
        // Utiliza el método getSubscribedEvents para registrar los listeners
        foreach (static::getSubscribedEvents() as $event => $method) {
            $dispatcher->listen($event, function (EventContract $event) use ($method) {
                $this->{$method}($event);
            });
        }
    }
    
    /**
     * Manejador para cuando la aplicación termina de inicializarse
     *
     * @param EventContract $event
     * @return void
     */
    public function onApplicationBootstrapped(EventContract $event): void
    {
        // Por ejemplo, podríamos inicializar caches de aplicación aquí
        // o registrar recursos adicionales después del bootstrap
        
        // Este método se ejecutará cuando se dispare el evento application.bootstrapped
    }
    
    /**
     * Manejador para cuando la aplicación está terminando
     *
     * @param EventContract $event
     * @return void
     */
    public function onApplicationTerminating(EventContract $event): void
    {
        // Por ejemplo, podríamos realizar limpiezas finales aquí
        // o guardar estadísticas de la solicitud actual
        
        // Este método se ejecutará cuando se dispare el evento application.terminating
    }
}
