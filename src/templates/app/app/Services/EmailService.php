<?php

namespace App\Services;

/**
 * Servicio de email simple para la aplicación
 */
class EmailService
{
    /**
     * Indica si el servicio está en modo de prueba
     */
    private bool $testMode;
    
    /**
     * Almacena los emails enviados en modo de prueba
     */
    private array $sentEmails = [];
    
    /**
     * Constructor con configuración inicial
     */
    public function __construct(bool $testMode = false)
    {
        $this->testMode = $testMode;
    }
    
    /**
     * Envía un email
     *
     * @param string $to Destinatario
     * @param string $subject Asunto
     * @param string $template Plantilla de email
     * @param array $data Datos para la plantilla
     * @return bool
     */
    public function send(string $to, string $subject, string $template, array $data = []): bool
    {
        $content = "Contenido generado usando la plantilla $template con datos: " . json_encode($data);
        
        // En modo de prueba, almacenamos el email en lugar de enviarlo
        if ($this->testMode) {
            $this->sentEmails[] = [
                'to' => $to,
                'subject' => $subject,
                'template' => $template,
                'data' => $data,
                'content' => $content,
                'sent_at' => date('Y-m-d H:i:s')
            ];
            return true;
        }
        
        // Aquí iría la lógica real de envío de email
        // Por ejemplo, usando PHPMailer, Swift Mailer, etc.
        
        return true; // Simulamos éxito en el envío
    }
    
    /**
     * Obtiene los emails enviados en modo de prueba
     *
     * @return array
     */
    public function getSentEmails(): array
    {
        return $this->sentEmails;
    }
    
    /**
     * Limpia la lista de emails enviados
     *
     * @return void
     */
    public function clearSentEmails(): void
    {
        $this->sentEmails = [];
    }
    
    /**
     * Obtiene el último email enviado
     *
     * @return array|null
     */
    public function getLastSentEmail(): ?array
    {
        if (empty($this->sentEmails)) {
            return null;
        }
        
        return $this->sentEmails[count($this->sentEmails) - 1];
    }
}
