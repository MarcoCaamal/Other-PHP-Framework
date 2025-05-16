<?php

namespace LightWeight\Providers;

use DI\Container as DIContainer;
use LightWeight\Mail\Contracts\MailerContract;
use LightWeight\Mail\Mailer;
use LightWeight\Providers\Contracts\ServiceProviderContract;
use LightWeight\View\Contracts\ViewContract;

/**
 * Proveedor de servicios para el sistema de correo electrónico
 */
class MailServiceProvider implements ServiceProviderContract
{
    /**
     * Registra los servicios relacionados con el correo electrónico en el contenedor
     *
     * @param DIContainer $container Contenedor de inyección de dependencias
     * @return void
     */
    public function registerServices(DIContainer $container): void
    {
        // Registrar el servicio Mailer como singleton
        $container->set(MailerContract::class, function () use ($container) {
            // Obtener el driver predeterminado de la configuración
            $defaultDriver = config('mail.default', 'phpmailer');
            
            // Intentar resolver el motor de vistas del contenedor
            $viewEngine = null;
            if ($container->has(ViewContract::class)) {
                $viewEngine = $container->get(ViewContract::class);
            }
            
            // Crear y configurar el servicio de correo con el motor de vistas
            return new Mailer($defaultDriver, $viewEngine);
        });
        
        // También hacerlo disponible como 'mailer'
        $container->set('mailer', \DI\get(MailerContract::class));
    }
}
