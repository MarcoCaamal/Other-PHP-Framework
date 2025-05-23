<?php

namespace LightWeight\Mail\Drivers;

use LightWeight\Mail\Contracts\MailDriverContract;

/**
 * Driver para envío de emails usando el sistema de logs
 * Útil para entornos de desarrollo donde no queremos enviar emails reales
 */
class LogDriver implements MailDriverContract
{
    /**
     * Datos del email que se va a enviar
     */
    protected array $emailData = [
        'from' => '',
        'from_name' => '',
        'to' => [],
        'cc' => [],
        'bcc' => [],
        'subject' => '',
        'html_body' => '',
        'text_body' => '',
        'attachments' => [],
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        // No se requiere configuración especial
    }

    /**
     * Establece el remitente del email
     *
     * @param string $address Dirección de email del remitente
     * @param string|null $name Nombre del remitente (opcional)
     * @return self
     */
    public function setFrom(string $address, ?string $name = null): self
    {
        $this->emailData['from'] = $address;
        $this->emailData['from_name'] = $name ?? '';
        return $this;
    }

    /**
     * Establece el destinatario del email
     *
     * @param string $address Dirección de email del destinatario
     * @param string|null $name Nombre del destinatario (opcional)
     * @return self
     */
    public function setTo(string $address, ?string $name = null): self
    {
        $this->emailData['to'] = [
            'address' => $address,
            'name' => $name ?? '',
        ];
        return $this;
    }

    /**
     * Añade un destinatario en copia (CC)
     *
     * @param string $address Dirección de email del destinatario
     * @param string|null $name Nombre del destinatario (opcional)
     * @return self
     */
    public function addCC(string $address, ?string $name = null): self
    {
        $this->emailData['cc'][] = [
            'address' => $address,
            'name' => $name ?? '',
        ];
        return $this;
    }

    /**
     * Añade un destinatario en copia oculta (BCC)
     *
     * @param string $address Dirección de email del destinatario
     * @param string|null $name Nombre del destinatario (opcional)
     * @return self
     */
    public function addBCC(string $address, ?string $name = null): self
    {
        $this->emailData['bcc'][] = [
            'address' => $address,
            'name' => $name ?? '',
        ];
        return $this;
    }

    /**
     * Establece el asunto del email
     *
     * @param string $subject Asunto del email
     * @return self
     */
    public function setSubject(string $subject): self
    {
        $this->emailData['subject'] = $subject;
        return $this;
    }

    /**
     * Establece el cuerpo HTML del email
     *
     * @param string $body Contenido HTML del email
     * @return self
     */
    public function setHtmlBody(string $body): self
    {
        $this->emailData['html_body'] = $body;
        return $this;
    }

    /**
     * Establece el cuerpo de texto plano del email
     *
     * @param string $body Contenido de texto plano del email
     * @return self
     */
    public function setTextBody(string $body): self
    {
        $this->emailData['text_body'] = $body;
        return $this;
    }

    /**
     * Añade un archivo adjunto al email
     *
     * @param string $path Ruta al archivo
     * @param string|null $name Nombre del archivo (opcional)
     * @return self
     */
    public function addAttachment(string $path, ?string $name = null): self
    {
        $this->emailData['attachments'][] = [
            'path' => $path,
            'name' => $name ?? basename($path),
        ];
        return $this;
    }

    /**
     * Añade un archivo adjunto desde una cadena de datos
     *
     * @param string $content Contenido del archivo
     * @param string $name Nombre del archivo
     * @param string|null $mimeType Tipo MIME del archivo (opcional)
     * @return self
     */
    public function addStringAttachment(string $content, string $name, ?string $mimeType = null): self
    {
        $this->emailData['attachments'][] = [
            'content' => substr($content, 0, 100) . '... [truncated]',
            'name' => $name,
            'mime_type' => $mimeType,
        ];
        return $this;
    }

    /**
     * Envía el email (en este caso, lo registra en el log)
     *
     * @return bool True siempre, ya que no hay envío real
     */
    public function send(): bool
    {
        $logMessage = $this->formatLogMessage();

        if (function_exists('log_info')) {
            log_info($logMessage);
        } else {
            error_log($logMessage);
        }

        return true;
    }

    /**
     * Formatea el mensaje para el log
     *
     * @return string
     */
    protected function formatLogMessage(): string
    {
        $message = "Email enviado (simulado):\n";
        $message .= "De: {$this->emailData['from']}";

        if (!empty($this->emailData['from_name'])) {
            $message .= " ({$this->emailData['from_name']})";
        }

        $message .= "\n";

        if (!empty($this->emailData['to'])) {
            $message .= "Para: {$this->emailData['to']['address']}";

            if (!empty($this->emailData['to']['name'])) {
                $message .= " ({$this->emailData['to']['name']})";
            }

            $message .= "\n";
        }

        if (!empty($this->emailData['cc'])) {
            $message .= "CC: ";
            $ccAddresses = [];

            foreach ($this->emailData['cc'] as $cc) {
                $ccAddress = $cc['address'];

                if (!empty($cc['name'])) {
                    $ccAddress .= " ({$cc['name']})";
                }

                $ccAddresses[] = $ccAddress;
            }

            $message .= implode(', ', $ccAddresses) . "\n";
        }

        if (!empty($this->emailData['bcc'])) {
            $message .= "BCC: ";
            $bccAddresses = [];

            foreach ($this->emailData['bcc'] as $bcc) {
                $bccAddress = $bcc['address'];

                if (!empty($bcc['name'])) {
                    $bccAddress .= " ({$bcc['name']})";
                }

                $bccAddresses[] = $bccAddress;
            }

            $message .= implode(', ', $bccAddresses) . "\n";
        }

        $message .= "Asunto: {$this->emailData['subject']}\n";

        if (!empty($this->emailData['text_body'])) {
            $message .= "Cuerpo (texto): " . substr($this->emailData['text_body'], 0, 300) . "...\n";
        }

        if (!empty($this->emailData['html_body'])) {
            $htmlExcerpt = strip_tags(substr($this->emailData['html_body'], 0, 300));
            $message .= "Cuerpo (HTML): " . $htmlExcerpt . "...\n";
        }

        if (!empty($this->emailData['attachments'])) {
            $message .= "Adjuntos: " . count($this->emailData['attachments']) . "\n";

            foreach ($this->emailData['attachments'] as $attachment) {
                if (isset($attachment['path'])) {
                    $message .= "- {$attachment['name']} ({$attachment['path']})\n";
                } else {
                    $message .= "- {$attachment['name']} (contenido string)\n";
                }
            }
        }

        return $message;
    }

    /**
     * Reinicia el objeto de email para un nuevo envío
     *
     * @return self
     */
    public function reset(): self
    {
        $this->emailData = [
            'from' => '',
            'from_name' => '',
            'to' => [],
            'cc' => [],
            'bcc' => [],
            'subject' => '',
            'html_body' => '',
            'text_body' => '',
            'attachments' => [],
        ];

        return $this;
    }
}
