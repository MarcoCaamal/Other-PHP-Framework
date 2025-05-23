<?php

namespace LightWeight\Mail\Contracts;

/**
 * Contrato para el servicio principal de correo electrónico
 */
interface MailerContract
{
    /**
     * Envía un email simple
     *
     * @param string $to Dirección de email del destinatario
     * @param string $subject Asunto del email
     * @param string $message Contenido del email (puede ser HTML)
     * @param array $options Opciones adicionales como 'cc', 'bcc', 'attachments', etc.
     * @return bool True si el email se envió correctamente, False en caso contrario
     */
    public function send(string $to, string $subject, string $message, array $options = []): bool;

    /**
     * Envía un email usando una plantilla
     *
     * @param string $to Dirección de email del destinatario
     * @param string $subject Asunto del email
     * @param string $template Nombre de la plantilla a usar
     * @param array $data Datos para pasar a la plantilla
     * @param array $options Opciones adicionales como 'cc', 'bcc', 'attachments', etc.
     * @return bool True si el email se envió correctamente, False en caso contrario
     */
    public function sendTemplate(string $to, string $subject, string $template, array $data = [], array $options = []): bool;

    /**
     * Obtiene el driver actual
     *
     * @return \LightWeight\Mail\Contracts\MailDriverContract
     */
    public function getDriver(): MailDriverContract;

    /**
     * Establece el driver a utilizar
     *
     * @param string $driver Nombre del driver ('phpmailer', 'smtp', 'log', etc.)
     * @return self
     */
    public function setDriver(string $driver): self;
}
