<?php

namespace LightWeight\Providers;

use LightWeight\Container\Container;
use LightWeight\Providers\Contracts\ServiceProviderContract;

/**
 * Clase base para los proveedores de servicios
 *
 * Implementa el nuevo método getDefinitions() con un array vacío por defecto
 * para mantener retrocompatibilidad con los providers existentes.
 */
abstract class ServiceProvider implements ServiceProviderContract
{
    /**
     * Proporciona definiciones para el contenedor antes de su compilación
     * Los providers hijos deberían sobrescribir este método para sus definiciones específicas
     *
     * @return array Definiciones para PHP-DI
     */
    public function getDefinitions(): array
    {
        return [];
    }

    /**
     * Registra servicios en el contenedor después de su construcción
     * Este método debe ser implementado por las clases hijas
     *
     * @param Container $serviceContainer
     * @return void
     */
    abstract public function registerServices(Container $serviceContainer);
}
