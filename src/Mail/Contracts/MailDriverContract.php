<?php

namespace LightWeight\Mail\Contracts;

/**
 * Contrato para proveedores de email
 */
interface MailDriverContract
{
    /**
     * Establece el remitente del email
     *
     * @param string $address Dirección de email del remitente
     * @param string|null $name Nombre del remitente (opcional)
     * @return self
     */
    public function setFrom(string $address, ?string $name = null): self;

    /**
     * Establece el destinatario del email
     *
     * @param string $address Dirección de email del destinatario
     * @param string|null $name Nombre del destinatario (opcional)
     * @return self
     */
    public function setTo(string $address, ?string $name = null): self;

    /**
     * Añade un destinatario en copia (CC)
     *
     * @param string $address Dirección de email del destinatario
     * @param string|null $name Nombre del destinatario (opcional)
     * @return self
     */
    public function addCC(string $address, ?string $name = null): self;

    /**
     * Añade un destinatario en copia oculta (BCC)
     *
     * @param string $address Dirección de email del destinatario
     * @param string|null $name Nombre del destinatario (opcional)
     * @return self
     */
    public function addBCC(string $address, ?string $name = null): self;

    /**
     * Establece el asunto del email
     *
     * @param string $subject Asunto del email
     * @return self
     */
    public function setSubject(string $subject): self;

    /**
     * Establece el cuerpo HTML del email
     *
     * @param string $body Contenido HTML del email
     * @return self
     */
    public function setHtmlBody(string $body): self;

    /**
     * Establece el cuerpo de texto plano del email
     *
     * @param string $body Contenido de texto plano del email
     * @return self
     */
    public function setTextBody(string $body): self;

    /**
     * Añade un archivo adjunto al email
     *
     * @param string $path Ruta al archivo
     * @param string|null $name Nombre del archivo (opcional)
     * @return self
     */
    public function addAttachment(string $path, ?string $name = null): self;

    /**
     * Añade un archivo adjunto desde una cadena de datos
     *
     * @param string $content Contenido del archivo
     * @param string $name Nombre del archivo
     * @param string|null $mimeType Tipo MIME del archivo (opcional)
     * @return self
     */
    public function addStringAttachment(string $content, string $name, ?string $mimeType = null): self;

    /**
     * Envía el email
     *
     * @return bool True si el email se envió correctamente, False en caso contrario
     */
    public function send(): bool;

    /**
     * Reinicia el objeto de email para un nuevo envío
     *
     * @return self
     */
    public function reset(): self;
}
