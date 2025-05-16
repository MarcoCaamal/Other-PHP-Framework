# Referencia de la API de Correo Electrónico

Esta página documenta en detalle las clases y métodos que componen el sistema de correo electrónico de LightWeight.

## Contratos (Interfaces)

### `LightWeight\Mail\Contracts\MailerContract`

Interface principal del servicio de correo electrónico.

```php
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
    
    /**
     * Registra un nuevo driver
     *
     * @param string $name Nombre del driver
     * @param string $class Clase que implementa MailDriverContract
     * @return self
     */
    public function registerDriver(string $name, string $class): self;
}
```

### `LightWeight\Mail\Contracts\MailDriverContract`

Interface que deben implementar todos los drivers de correo.

```php
interface MailDriverContract
{
    /**
     * Reinicia todas las propiedades para un nuevo email
     *
     * @return self
     */
    public function reset(): self;
    
    /**
     * Establece el remitente del email
     *
     * @param string $email Dirección de email del remitente
     * @param string|null $name Nombre del remitente (opcional)
     * @return self
     */
    public function setFrom(string $email, ?string $name = null): self;
    
    /**
     * Establece el destinatario principal del email
     *
     * @param string $email Dirección de email del destinatario
     * @return self
     */
    public function setTo(string $email): self;
    
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
     * @param string $html Contenido HTML del email
     * @return self
     */
    public function setHtmlBody(string $html): self;
    
    /**
     * Establece el cuerpo de texto plano del email (opcional)
     *
     * @param string|null $text Contenido de texto plano
     * @return self
     */
    public function setTextBody(?string $text): self;
    
    /**
     * Añade un destinatario en copia (CC)
     *
     * @param string $email Dirección de email
     * @return self
     */
    public function addCC(string $email): self;
    
    /**
     * Añade un destinatario en copia oculta (BCC)
     *
     * @param string $email Dirección de email
     * @return self
     */
    public function addBCC(string $email): self;
    
    /**
     * Añade un archivo adjunto al email
     *
     * @param string $path Ruta completa al archivo
     * @param string|null $name Nombre alternativo para el archivo (opcional)
     * @return self
     */
    public function addAttachment(string $path, ?string $name = null): self;
    
    /**
     * Envía el email con la configuración actual
     *
     * @return bool True si el email se envió correctamente, False en caso contrario
     */
    public function send(): bool;
}
```

## Clase Principal

### `LightWeight\Mail\Mailer`

Implementación principal del servicio de correo, utiliza el patrón Strategy para cambiar entre diferentes drivers.

```php
class Mailer implements MailerContract
{
    /**
     * Constructor
     *
     * @param string $defaultDriver Driver por defecto a utilizar
     * @param ViewContract|null $viewEngine Motor de vistas para renderizar plantillas (opcional)
     */
    public function __construct(string $defaultDriver = 'phpmailer', ?ViewContract $viewEngine = null);
    
    /**
     * Envía un email simple
     */
    public function send(string $to, string $subject, string $message, array $options = []): bool;
    
    /**
     * Envía un email usando una plantilla
     */
    public function sendTemplate(string $to, string $subject, string $template, array $data = [], array $options = []): bool;
    
    /**
     * Obtiene el driver actual
     */
    public function getDriver(): MailDriverContract;
    
    /**
     * Establece el driver a utilizar
     */
    public function setDriver(string $driver): self;
    
    /**
     * Registra un nuevo driver
     */
    public function registerDriver(string $name, string $class): self;
    
    /**
     * Renderiza una plantilla con los datos proporcionados
     *
     * @param string $template Nombre de la plantilla
     * @param array $data Datos para la plantilla
     * @return string Contenido HTML renderizado
     * @throws \LightWeight\Mail\Exceptions\MailerException Si la plantilla no existe
     */
    protected function renderTemplate(string $template, array $data): string;
}
```

## Drivers de Correo

### `LightWeight\Mail\Drivers\PhpMailerDriver`

Driver que utiliza la librería PHPMailer para enviar correos mediante SMTP.

```php
class PhpMailerDriver implements MailDriverContract
{
    // Implementa todos los métodos de MailDriverContract
    // Utiliza PHPMailer internamente para enviar los correos
}
```

### `LightWeight\Mail\Drivers\LogDriver`

Driver que registra los correos en el log en lugar de enviarlos. Útil para entornos de desarrollo.

```php
class LogDriver implements MailDriverContract
{
    // Implementa todos los métodos de MailDriverContract
    // Registra información sobre los correos en logs en lugar de enviarlos
}
```

## Proveedor de Servicios

### `LightWeight\Providers\MailServiceProvider`

Registra los servicios de correo en el contenedor de dependencias.

```php
class MailServiceProvider implements ServiceProviderContract
{
    /**
     * Registra los servicios relacionados con el correo electrónico en el contenedor
     *
     * @param DIContainer $container Contenedor de inyección de dependencias
     * @return void
     */
    public function registerServices(DIContainer $container): void;
}
```

## Funciones Helper

### `LightWeight\Helpers\mailSend()`

```php
/**
 * Envía un correo electrónico
 *
 * @param string $to Dirección de email del destinatario
 * @param string $subject Asunto del email
 * @param string $message Contenido del email (puede ser HTML)
 * @param array $options Opciones adicionales como 'cc', 'bcc', 'attachments', etc.
 * @return bool True si el email se envió correctamente, False en caso contrario
 */
function mailSend(string $to, string $subject, string $message, array $options = []): bool;
```

### `LightWeight\Helpers\mailTemplate()`

```php
/**
 * Envía un correo electrónico usando una plantilla
 *
 * @param string $to Dirección de email del destinatario
 * @param string $subject Asunto del email
 * @param string $template Nombre de la plantilla a usar
 * @param array $data Datos para pasar a la plantilla
 * @param array $options Opciones adicionales como 'cc', 'bcc', 'attachments', etc.
 * @return bool True si el email se envió correctamente, False en caso contrario
 */
function mailTemplate(string $to, string $subject, string $template, array $data = [], array $options = []): bool;
```

### `LightWeight\Helpers\mailDriver()`

```php
/**
 * Obtiene o establece el driver de correo electrónico
 *
 * @param string|null $driver Si se proporciona, establece el driver
 * @return \LightWeight\Mail\Contracts\MailDriverContract|\LightWeight\Mail\Contracts\MailerContract
 */
function mailDriver(?string $driver = null);
```

## Excepciones

### `LightWeight\Mail\Exceptions\MailerException`

```php
/**
 * Excepción específica para errores relacionados con el envío de correos
 */
class MailerException extends \Exception
{
    // Métodos específicos para manejar errores de correo
}
```
