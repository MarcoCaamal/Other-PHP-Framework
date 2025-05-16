# Sistema de Correo Electrónico

LightWeight ofrece un potente sistema de correo electrónico que permite enviar emails de manera sencilla desde tu aplicación. El sistema está diseñado siguiendo el patrón Strategy, lo que permite cambiar fácilmente entre diferentes drivers de correo.

> Para una referencia detallada de todas las clases, interfaces y métodos, consulta la [Referencia de la API de Correo Electrónico](mail-api-reference.md).

## Características Principales

- **Múltiples drivers**: Soporte para diferentes proveedores de correo (PHPMailer, Log, etc)
- **Plantillas de correo**: Envío de correos utilizando plantillas HTML
- **Funciones helper**: API sencilla para enviar correos desde cualquier parte de la aplicación
- **Adjuntos**: Soporte para archivos adjuntos en los correos
- **CC y BCC**: Posibilidad de añadir destinatarios en copia y copia oculta
- **Integración con el motor de vistas**: Utiliza el mismo motor de vistas de la aplicación para renderizar las plantillas

## Configuración

El sistema de correo se configura en el archivo `config/mail.php`:

```php
return [
    // Driver por defecto ('phpmailer', 'log', etc)
    'default' => env('MAIL_DRIVER', 'phpmailer'),
    
    // Configuración para PHPMailer
    'phpmailer' => [
        'host' => env('MAIL_HOST', 'smtp.example.com'),
        'port' => env('MAIL_PORT', 587),
        'username' => env('MAIL_USERNAME', 'user@example.com'),
        'password' => env('MAIL_PASSWORD', 'secret'),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'from_address' => env('MAIL_FROM_ADDRESS', 'no-reply@example.com'),
        'from_name' => env('MAIL_FROM_NAME', 'LightWeight App'),
    ],
    
    // Configuración para el driver de log (útil para desarrollo)
    'log' => [
        'channel' => env('MAIL_LOG_CHANNEL', 'mail'),
    ],
];
```

Además, debes configurar tus credenciales en el archivo `.env`:

```
MAIL_DRIVER=phpmailer
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=user@example.com
MAIL_PASSWORD=secret
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="Mi Aplicación"
```

## Uso Básico

### Enviar un Correo Simple

```php
// Usando la función helper
mailSend(
    'destinatario@ejemplo.com',
    'Asunto del correo',
    '<p>Este es el contenido del correo en HTML</p>'
);

// O utilizando el servicio directamente
$mailer = app(\LightWeight\Mail\Contracts\MailerContract::class);
$mailer->send(
    'destinatario@ejemplo.com',
    'Asunto del correo',
    '<p>Este es el contenido del correo en HTML</p>'
);
```

### Enviar un Correo con Opciones Adicionales

```php
mailSend(
    'destinatario@ejemplo.com',
    'Asunto del correo',
    '<p>Este es el contenido del correo en HTML</p>',
    [
        'from' => 'remitente@ejemplo.com',
        'from_name' => 'Mi Nombre',
        'cc' => ['copia1@ejemplo.com', 'copia2@ejemplo.com'],
        'bcc' => 'copiaoculta@ejemplo.com',
        'text' => 'Versión en texto plano del correo (opcional)',
        'attachments' => [
            '/ruta/al/archivo.pdf',
            [
                'path' => '/ruta/al/archivo2.jpg',
                'name' => 'imagen.jpg'
            ]
        ]
    ]
);
```

### Enviar un Correo con Plantilla

Las plantillas de correo se almacenan en `resources/views/emails/` y utilizan el motor de vistas de la aplicación.

```php
// Usando la función helper
mailTemplate(
    'destinatario@ejemplo.com',
    'Bienvenido a nuestra aplicación',
    'welcome',  // Nombre de la plantilla (resources/views/emails/welcome.php)
    [
        'userName' => 'John Doe',
        'activationLink' => 'https://example.com/activate/123'
    ]
);

// O usando la notación de puntos para plantillas en subdirectorios
mailTemplate(
    'destinatario@ejemplo.com',
    'Confirmación de compra',
    'orders.confirmation',  // resources/views/emails/orders/confirmation.php
    [
        'orderNumber' => '12345',
        'items' => $items
    ]
);
```

### Cambiar el Driver en Tiempo de Ejecución

```php
// Obtener el driver actual
$currentDriver = mailDriver();

// Cambiar al driver de log (útil para pruebas)
mailDriver('log');

// Enviar el correo usando el driver de log
mailSend('test@example.com', 'Prueba', '<p>Este correo se registrará en el log</p>');

// Restaurar el driver original
mailDriver('phpmailer');
```

## Creación de Plantillas de Correo

Las plantillas de correo se almacenan en `resources/views/emails/` y pueden utilizar todas las características del motor de vistas, como secciones, inclusión de parciales, etc.

Ejemplo de plantilla de bienvenida (`resources/views/emails/welcome.php`):

```php
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a nuestra plataforma</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #f5f5f5;
        }
        .content {
            padding: 20px 0;
        }
        .button {
            display: inline-block;
            background-color: #4a89dc;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Bienvenido/a a nuestra plataforma!</h1>
        </div>
        
        <div class="content">
            <p>Hola <?= htmlspecialchars($userName) ?>,</p>
            
            <p>¡Gracias por registrarte en nuestra plataforma! Estamos emocionados de tenerte como miembro.</p>
            
            <p>Con tu cuenta podrás:</p>
            <ul>
                <li>Acceder a todas nuestras funcionalidades</li>
                <li>Gestionar tus datos personales</li>
                <li>Participar en nuestra comunidad</li>
                <li>Y mucho más...</li>
            </ul>
            
            <p>Si tienes alguna pregunta, no dudes en contactarnos respondiendo a este correo electrónico.</p>
            
            <p>
                <a href="<?= config('app.url', 'https://yoursite.com') ?>/login" class="button">Iniciar sesión</a>
            </p>
        </div>
        
        <div class="footer">
            <p>&copy; <?= date('Y') ?> LightWeight Framework. Todos los derechos reservados.</p>
            <p>Este es un correo electrónico automático, por favor no responda a este mensaje.</p>
        </div>
    </div>
</body>
</html>
```

## Drivers Personalizados

Puedes crear drivers personalizados para el sistema de correo implementando la interfaz `LightWeight\Mail\Contracts\MailDriverContract`:

```php
namespace App\Mail\Drivers;

use LightWeight\Mail\Contracts\MailDriverContract;

class CustomMailDriver implements MailDriverContract
{
    // Implementación de los métodos necesarios
    public function reset(): self { /* ... */ }
    public function setFrom(string $email, ?string $name = null): self { /* ... */ }
    public function setTo(string $email): self { /* ... */ }
    public function setSubject(string $subject): self { /* ... */ }
    public function setHtmlBody(string $html): self { /* ... */ }
    public function setTextBody(?string $text): self { /* ... */ }
    public function addCC(string $email): self { /* ... */ }
    public function addBCC(string $email): self { /* ... */ }
    public function addAttachment(string $path, ?string $name = null): self { /* ... */ }
    public function send(): bool { /* ... */ }
}
```

Luego, registra tu driver personalizado:

```php
use LightWeight\Mail\Contracts\MailerContract;

// En un proveedor de servicios o en el bootstrap de la aplicación
$mailer = app(MailerContract::class);
$mailer->registerDriver('custom', \App\Mail\Drivers\CustomMailDriver::class);

// Ahora puedes usar tu driver personalizado
mailDriver('custom');
```

## Pruebas

Para probar el envío de correos sin enviarlos realmente, puedes utilizar el driver de log:

```php
// Cambiar temporalmente al driver de log
mailDriver('log');

// Enviar el correo (se registrará en el log en lugar de enviarse)
mailSend('test@example.com', 'Prueba', '<p>Correo de prueba</p>');

// Restaurar el driver original si es necesario
mailDriver('phpmailer');
```

O crear un driver de prueba específico para tus tests:

```php
class TestMailDriver implements MailDriverContract
{
    public $sentEmails = [];
    
    // Implementación de métodos...
    
    public function send(): bool
    {
        $this->sentEmails[] = [
            'to' => $this->to,
            'subject' => $this->subject,
            'body' => $this->htmlBody,
            // Otros campos...
        ];
        return true;
    }
}
```

## Consideraciones de Seguridad

- Evita incluir información sensible en los correos
- Utiliza siempre `htmlspecialchars()` o el escape automático del motor de vistas al mostrar datos proporcionados por el usuario
- Mantén tus credenciales SMTP seguras en el archivo `.env` (no las incluyas en el control de versiones)
- Considera el uso de SPF, DKIM y DMARC para mejorar la entregabilidad y seguridad de tus correos

## Solución de Problemas

### Los correos no se envían

Comprueba:
- La configuración SMTP (host, puerto, credenciales)
- El firewall o restricciones de red que puedan bloquear el puerto SMTP
- Los logs de la aplicación para ver errores específicos
- Que el driver de correo esté correctamente configurado

### Los correos van a la carpeta de spam

Posibles soluciones:
- Configurar correctamente los registros SPF, DKIM y DMARC
- Utilizar un dominio real para el remitente (no correos gratuitos como Gmail o Hotmail)
- Evitar palabras que puedan activar filtros de spam
- Mantener un buen balance entre texto e imágenes

### Plantillas no encontradas

Si recibes errores de plantillas no encontradas:
- Verifica que la plantilla exista en `resources/views/emails/`
- Comprueba que estás usando la notación correcta (puntos para subdirectorios)
- Asegúrate de que los permisos de archivo sean correctos
