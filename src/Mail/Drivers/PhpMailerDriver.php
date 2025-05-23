<?php

namespace LightWeight\Mail\Drivers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use LightWeight\Mail\Contracts\MailDriverContract;

/**
 * Driver para envío de emails usando PHPMailer
 */
class PhpMailerDriver implements MailDriverContract
{
    /**
     * Instancia de PHPMailer
     */
    protected PHPMailer $mailer;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mailer = new PHPMailer(true); // true habilita excepciones
        $this->configure();
    }

    /**
     * Configura PHPMailer según las opciones de configuración
     */
    protected function configure(): void
    {
        // Configuración del servidor
        $this->mailer->isSMTP();
        $this->mailer->Host = config('mail.host', 'smtp.example.com');
        $this->mailer->SMTPAuth = (bool)config('mail.auth', true);
        $this->mailer->Username = config('mail.username', '');
        $this->mailer->Password = config('mail.password', '');

        // Configuración de cifrado
        $encryption = config('mail.encryption', 'ENCRYPTION_STARTTLS');
        if ($encryption && defined('PHPMailer\\PHPMailer\\PHPMailer::' . $encryption)) {
            $this->mailer->SMTPSecure = constant('PHPMailer\\PHPMailer\\PHPMailer::' . $encryption);
        }

        $this->mailer->Port = (int)config('mail.port', 587);

        // Configuración de depuración
        $this->mailer->SMTPDebug = (int)config('mail.debug', 0);

        // Configuración del remitente por defecto
        $fromAddress = config('mail.from.address', 'from@example.com');
        $fromName = config('mail.from.name', 'Example');

        if ($fromAddress) {
            $this->setFrom($fromAddress, $fromName);
        }

        // Configuración de codificación
        $this->mailer->CharSet = config('mail.charset', 'UTF-8');
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
        try {
            $this->mailer->setFrom($address, $name ?? '');
        } catch (Exception $e) {
            // Log error
            if (function_exists('log_error')) {
                log_error("Error al establecer remitente: {$e->getMessage()}");
            }
        }

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
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($address, $name ?? '');
        } catch (Exception $e) {
            // Log error
            if (function_exists('log_error')) {
                log_error("Error al establecer destinatario: {$e->getMessage()}");
            }
        }

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
        try {
            $this->mailer->addCC($address, $name ?? '');
        } catch (Exception $e) {
            // Log error
            if (function_exists('log_error')) {
                log_error("Error al añadir CC: {$e->getMessage()}");
            }
        }

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
        try {
            $this->mailer->addBCC($address, $name ?? '');
        } catch (Exception $e) {
            // Log error
            if (function_exists('log_error')) {
                log_error("Error al añadir BCC: {$e->getMessage()}");
            }
        }

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
        $this->mailer->Subject = $subject;
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
        $this->mailer->isHTML(true);
        $this->mailer->Body = $body;

        // Generar automáticamente una versión de texto plano si no se ha establecido
        if (empty($this->mailer->AltBody)) {
            $this->mailer->AltBody = strip_tags($body);
        }

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
        $this->mailer->AltBody = $body;
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
        try {
            $this->mailer->addAttachment($path, $name ?? '');
        } catch (Exception $e) {
            // Log error
            if (function_exists('log_error')) {
                log_error("Error al añadir adjunto: {$e->getMessage()}");
            }
        }

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
        try {
            $this->mailer->addStringAttachment($content, $name, 'base64', $mimeType ?? '');
        } catch (Exception $e) {
            // Log error
            if (function_exists('log_error')) {
                log_error("Error al añadir adjunto desde string: {$e->getMessage()}");
            }
        }

        return $this;
    }

    /**
     * Envía el email
     *
     * @return bool True si el email se envió correctamente, False en caso contrario
     */
    public function send(): bool
    {
        try {
            return $this->mailer->send();
        } catch (Exception $e) {
            // Log error
            if (function_exists('log_error')) {
                log_error("Error al enviar email: {$e->getMessage()}");
            }
            return false;
        }
    }

    /**
     * Reinicia el objeto de email para un nuevo envío
     *
     * @return self
     */
    public function reset(): self
    {
        $this->mailer->clearAddresses();
        $this->mailer->clearAttachments();
        $this->mailer->clearCCs();
        $this->mailer->clearBCCs();
        $this->mailer->clearReplyTos();
        $this->mailer->Subject = '';
        $this->mailer->Body = '';
        $this->mailer->AltBody = '';

        return $this;
    }
}
