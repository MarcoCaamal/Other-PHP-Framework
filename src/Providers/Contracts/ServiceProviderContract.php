<?php

namespace LightWeight\Providers\Contracts;

use LightWeight\Container\Container;

interface ServiceProviderContract
{
    /**
     * Registra servicios en el contenedor después de su construcción
     * Para configuraciones, eventos, y otras operaciones post-construcción
     * 
     * @param Container $serviceContainer
     * @return void
     */
    public function registerServices(Container $serviceContainer);
    
    /**
     * Proporciona definiciones para el contenedor antes de su construcción
     * Estas definiciones se pueden compilar/cachear
     * 
     * @return array Definiciones para PHP-DI
     */
    public function getDefinitions(): array;
}
