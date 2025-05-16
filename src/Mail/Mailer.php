<?php

namespace LightWeight\Mail;

use LightWeight\Mail\Contracts\MailDriverContract;
use LightWeight\Mail\Contracts\MailerContract;
use LightWeight\Mail\Exceptions\MailerException;
use LightWeight\View\Contracts\ViewContract;

/**
 * Servicio principal de correo electrónico
 */
class Mailer implements MailerContract
{
    /**
     * Driver actual para el envío de correos
     */
    protected MailDriverContract $driver;
    
    /**
     * Mapa de drivers disponibles
     *
     * @var array<string, string>
     */
    protected array $drivers = [
        'phpmailer' => \LightWeight\Mail\Drivers\PhpMailerDriver::class,
        'log' => \LightWeight\Mail\Drivers\LogDriver::class,
    ];
    
    /**
     * Motor de vistas para renderizar plantillas
     */
    protected ?ViewContract $viewEngine = null;
    
    /**
     * Constructor
     *
     * @param string $defaultDriver Driver por defecto a utilizar
     * @param ViewContract|null $viewEngine Motor de vistas para renderizar plantillas (opcional)
     */
    public function __construct(string $defaultDriver = 'phpmailer', ?ViewContract $viewEngine = null)
    {
        $this->setDriver($defaultDriver);
        $this->viewEngine = $viewEngine;
    }
    
    /**
     * Envía un email simple
     *
     * @param string $to Dirección de email del destinatario
     * @param string $subject Asunto del email
     * @param string $message Contenido del email (puede ser HTML)
     * @param array $options Opciones adicionales como 'cc', 'bcc', 'attachments', etc.
     * @return bool True si el email se envió correctamente, False en caso contrario
     */
    public function send(string $to, string $subject, string $message, array $options = []): bool
    {
        // Reiniciar el driver para un nuevo email
        $this->driver->reset();
        
        // Establecer el remitente si se especifica en las opciones
        if (isset($options['from'])) {
            $fromName = $options['from_name'] ?? null;
            $this->driver->setFrom($options['from'], $fromName);
        }
        
        // Configurar el email
        $this->driver->setTo($to)
                     ->setSubject($subject)
                     ->setHtmlBody($message);
        
        // Añadir CC si se especifica
        if (isset($options['cc'])) {
            if (is_array($options['cc'])) {
                foreach ($options['cc'] as $cc) {
                    $this->driver->addCC($cc);
                }
            } else {
                $this->driver->addCC($options['cc']);
            }
        }
        
        // Añadir BCC si se especifica
        if (isset($options['bcc'])) {
            if (is_array($options['bcc'])) {
                foreach ($options['bcc'] as $bcc) {
                    $this->driver->addBCC($bcc);
                }
            } else {
                $this->driver->addBCC($options['bcc']);
            }
        }
        
        // Añadir adjuntos si se especifican
        if (isset($options['attachments'])) {
            if (is_array($options['attachments'])) {
                foreach ($options['attachments'] as $attachment) {
                    if (is_array($attachment)) {
                        $this->driver->addAttachment($attachment['path'], $attachment['name'] ?? null);
                    } else {
                        $this->driver->addAttachment($attachment);
                    }
                }
            } else {
                $this->driver->addAttachment($options['attachments']);
            }
        }
        
        // Establecer cuerpo de texto plano si se especifica
        if (isset($options['text'])) {
            $this->driver->setTextBody($options['text']);
        }
        
        // Enviar el email
        return $this->driver->send();
    }
    
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
    public function sendTemplate(string $to, string $subject, string $template, array $data = [], array $options = []): bool
    {
        // Renderizar la plantilla
        $message = $this->renderTemplate($template, $data);
        
        // Enviar el email usando el mensaje renderizado
        return $this->send($to, $subject, $message, $options);
    }
    
    /**
     * Obtiene el driver actual
     *
     * @return \LightWeight\Mail\Contracts\MailDriverContract
     */
    public function getDriver(): MailDriverContract
    {
        return $this->driver;
    }
    
    /**
     * Establece el driver a utilizar
     *
     * @param string $driver Nombre del driver ('phpmailer', 'smtp', 'log', etc.)
     * @return self
     * @throws \LightWeight\Mail\Exceptions\MailerException Si el driver no existe
     */
    public function setDriver(string $driver): self
    {
        if (!isset($this->drivers[$driver])) {
            throw new MailerException("El driver de correo '{$driver}' no está registrado.");
        }
        
        $driverClass = $this->drivers[$driver];
        $this->driver = new $driverClass();
        
        return $this;
    }
    
    /**
     * Registra un nuevo driver
     *
     * @param string $name Nombre del driver
     * @param string $class Clase que implementa MailDriverContract
     * @return self
     */
    public function registerDriver(string $name, string $class): self
    {
        $this->drivers[$name] = $class;
        return $this;
    }
    
    /**
     * Renderiza una plantilla con los datos proporcionados
     *
     * @param string $template Nombre de la plantilla
     * @param array $data Datos para la plantilla
     * @return string Contenido HTML renderizado
     * @throws \LightWeight\Mail\Exceptions\MailerException Si la plantilla no existe
     */
    protected function renderTemplate(string $template, array $data): string
    {
        // Si tenemos un motor de vistas, lo usamos (prioridad 1)
        if ($this->viewEngine !== null) {
            try {
                // Convertimos el nombre de la plantilla a formato de punto si no lo está
                $viewName = str_replace('/', '.', $template);
                if (!str_starts_with($viewName, 'emails.')) {
                    $viewName = 'emails.' . $viewName;
                }
                
                // Renderizamos sin layout para emails (false como tercer parámetro)
                return $this->viewEngine->render($viewName, $data, false);
            } catch (\Exception $e) {
                throw new MailerException("Error al renderizar la plantilla de email '{$template}': " . $e->getMessage());
            }
        }
        
        // Si no tenemos motor de vistas, intentamos obtener recursos mediante helpers (prioridad 2)
        if (function_exists('resourcesDirectory')) {
            $templatePath = resourcesDirectory() . '/views/emails/' . str_replace('.', '/', $template) . '.php';
            
            if (!file_exists($templatePath)) {
                throw new MailerException("La plantilla de email '{$template}' no existe en: {$templatePath}");
            }
            
            // Extraer variables para la vista
            extract($data);
            
            // Capturar salida
            ob_start();
            include $templatePath;
            return ob_get_clean();
        }
        
        // Método de respaldo si nada de lo anterior está disponible (prioridad 3)
        $basePath = dirname(__DIR__, 2);
        $templatePath = rtrim($basePath, '/') . '/resources/views/emails/' . str_replace('.', '/', $template) . '.php';
        
        if (!file_exists($templatePath)) {
            throw new MailerException("La plantilla de email '{$template}' no existe en: {$templatePath}");
        }
        
        // Extraer variables para la vista
        extract($data);
        
        // Capturar salida
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }
}
