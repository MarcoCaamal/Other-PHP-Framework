<?php

namespace App\Services;

/**
 * Servicio para envío de emails
 */
class EmailService
{
    /**
     * Modo de prueba para no enviar emails realmente
     */
    private bool $testMode;
    
    /**
     * Último email enviado (para pruebas)
     */
    private ?array $lastSentEmail = null;
    
    /**
     * Constructor
     */
    public function __construct(bool $testMode = false)
    {
        $this->testMode = $testMode;
    }
    
    /**
     * Envía un email
     *
     * @param string $to Dirección de email del destinatario
     * @param string $subject Asunto del email
     * @param string $template Nombre de la plantilla de email
     * @param array $data Datos para pasar a la plantilla
     * @return bool Si el email se envió correctamente
     */
    public function send(string $to, string $subject, string $template, array $data = []): bool
    {
        // Almacenar los datos del email para pruebas
        $this->lastSentEmail = [
            'to' => $to,
            'subject' => $subject,
            'template' => $template,
            'data' => $data
        ];
        
        // En modo prueba, no enviamos el email realmente
        if ($this->testMode) {
            return true;
        }
        
        // Aquí iría la lógica real de envío de email
        // Por ejemplo, usando PHPMailer, Symfony Mailer, etc.
        
        // Simular éxito
        return true;
    }
    
    /**
     * Obtiene el último email enviado (para pruebas)
     *
     * @return array|null Datos del último email enviado
     */
    public function getLastSentEmail(): ?array
    {
        return $this->lastSentEmail;
    }
}
