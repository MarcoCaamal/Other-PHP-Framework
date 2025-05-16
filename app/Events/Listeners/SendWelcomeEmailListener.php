<?php

namespace App\Events\Listeners;

use App\Services\EmailService;
use LightWeight\Events\Contracts\EventInterface;
use LightWeight\Events\Contracts\ListenerInterface;

/**
 * Listener para enviar emails de bienvenida a nuevos usuarios registrados
 */
class SendWelcomeEmailListener implements ListenerInterface
{
    /**
     * Constructor con inyección de dependencias
     * 
     * Inyectamos el servicio de email que necesitamos
     */
    public function __construct(
        private EmailService $emailService
    ) {
        // No necesitamos inicialización adicional
    }
    
    /**
     * Manejar el evento
     *
     * @param EventInterface $event
     * @return void
     */
    public function handle(EventInterface $event): void
    {
        // Obtener datos del usuario desde el evento
        $user = $event->getData()['user'] ?? null;
        
        if (!$user) {
            return;
        }
        
        // Enviar email de bienvenida utilizando nuestro servicio inyectado
        $this->emailService->send(
            $user->email,
            'Bienvenido a nuestra plataforma',
            'emails.welcome',
            ['userName' => $user->name]
        );
        
        // Log del envío (ejemplo)
        if (function_exists('log_info')) {
            log_info("Email de bienvenida enviado a {$user->email}");
        }
    }
}
