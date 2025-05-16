<?php

namespace LightWeight\Events\Contracts;

/**
 * Interfaz para suscriptores de eventos
 * 
 * Los suscriptores de eventos son clases que pueden registrar múltiples oyentes (listeners)
 * para diferentes eventos, agrupando la funcionalidad relacionada en una sola clase.
 */
interface EventSubscriberInterface
{
    /**
     * Obtiene los eventos manejados por este suscriptor
     * 
     * @return array<string, string> Mapa de eventos a métodos del manejador
     */
    public static function getSubscribedEvents(): array;
    
    /**
     * Registrar los oyentes para este suscriptor
     *
     * @param EventDispatcherInterface $dispatcher
     * @return void
     */
    public function subscribe(EventDispatcherInterface $dispatcher): void;
}
