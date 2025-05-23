<?php

namespace LightWeight\Providers;

use LightWeight\Container\Container;
use LightWeight\Mail\Contracts\MailerContract;
use LightWeight\Mail\Mailer;
use LightWeight\View\Contracts\ViewContract;

/**
 * Proveedor de servicios para el sistema de correo electrónico
 */
class MailServiceProvider extends ServiceProvider
{
    /**
     * Proporciona definiciones para el contenedor antes de su compilación
     *
     * @return array
     */
    public function getDefinitions(): array
    {
        return [
            MailerContract::class => \DI\factory(function (?ViewContract $viewEngine = null) {
                $defaultDriver = config('mail.default', 'phpmailer');
                return new Mailer($defaultDriver, $viewEngine);
            }),
            'mailer' => \DI\get(MailerContract::class)
        ];
    }

    /**
     * Registra los servicios relacionados con el correo electrónico en el contenedor
     *
     * @param Container $container Contenedor de inyección de dependencias
     * @return void
     */
    public function registerServices(Container $container)
    {
        // Todas las definiciones ya están configuradas en getDefinitions()
    }
}
