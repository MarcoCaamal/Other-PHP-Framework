# Sistema de Logging

> 🌐 [English Documentation](../en/logging-system.md)

El framework LightWeight incluye un sistema de logging robusto basado en [Monolog](https://github.com/Seldaek/monolog), una de las bibliotecas de logging más populares para PHP.

## Configuración básica

El sistema de logging se configura en el archivo `config/logging.php`. Este archivo define los canales de logging disponibles y la configuración para cada uno.

```php
return [
    // Canal de logging predeterminado
    'default_channel' => env('LOG_CHANNEL', 'daily'),

    // Nivel mínimo de logging
    'level' => env('LOG_LEVEL', 'debug'),

    // Canales de logging disponibles
    'channels' => [
        'single' => [
            'path' => storagePath('logs/lightweight.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'bubble' => true,
        ],
        
        'daily' => [
            'path' => storagePath('logs/lightweight.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 7,
            'bubble' => true,
        ],
        
        // Más canales...
    ],
];
```

## Uso básico

### Usar las funciones helper

La forma más sencilla de usar el sistema de logging es mediante las funciones helper:

```php
// Obtener la instancia del logger
$logger = logger();

// Registrar mensajes con diferentes niveles
logger()->debug('Mensaje de depuración');
logger()->info('Información general');
logger()->warning('Advertencia');
logger()->error('Error');
logger()->critical('Error crítico');
logger()->alert('Alerta');
logger()->emergency('Emergencia');

// Registrar con contexto adicional
logger()->info('Usuario creado', ['id' => $user->id, 'email' => $user->email]);

// Usar la función helper logMessage() para un enfoque más directo
logMessage('Mensaje de información'); // Nivel predeterminado: info
logMessage('Ocurrió un error', ['detalles' => $exception->getMessage()], 'error'); // Con nivel
```

### Usar la instancia del logger

También puedes acceder al logger a través de la instancia de la aplicación:

```php
// A través de la instancia de App
$logger = app()->log();

// Registrar mensajes
$logger->info('Mensaje de información');
$logger->error('Mensaje de error', ['context' => 'valor']);
```

## Configurando handlers personalizados

Puedes configurar handlers adicionales para tu logger mediante el método `pushHandler()`:

```php
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Level;

// Obtener la instancia del logger
$logger = logger();

// Agregar un handler de Slack para errores críticos
$logger->pushHandler(
    new SlackWebhookHandler(
        'https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXXXXXX',
        '#errors',
        'LightWeight Error Bot',
        true,
        null,
        false,
        false,
        Level::Critical
    )
);

// Ahora los errores críticos también se enviarán a Slack
$logger->critical('Error crítico en producción', ['user_id' => 123]);
```

## Logging de eventos

El framework incluye soporte para el logging automático de eventos a través del `EventServiceProvider`. Esto puede configurarse en el archivo `config/logging.php`:

```php
return [
    // ... otras configuraciones

    /**
     * Configuración de Logging de Eventos
     *
     * Ajustes para el registro automático de eventos despachados en la aplicación.
     */
    'event_logging' => [
        /**
         * Habilitar el logging de eventos.
         */
        'enabled' => env('LOG_EVENTS', false),
        
        /**
         * Eventos que no deben ser registrados incluso cuando el logging de eventos está habilitado.
         */
        'excluded_events' => [
            'application.bootstrapped',
            'router.matched',
            // Otros eventos a excluir...
        ],
    ],
];
```

Cuando está habilitado, el framework registrará automáticamente cada evento despachado en el sistema. El sistema de logging de eventos:

1. Captura todos los eventos a través de un listener especial registrado con el despachador de eventos
2. Formatea los datos del evento de manera consistente
3. Registra los eventos a través del logger configurado
4. Maneja de forma inteligente tanto los eventos que implementan `EventContract` como otros tipos de eventos

Esto es particularmente útil para:

- Depurar sistemas basados en eventos
- Seguimiento de actividades de usuario
- Monitorear operaciones del sistema
- Auditar el comportamiento de la aplicación

Para evitar un exceso de logging, puedes añadir eventos frecuentemente disparados a la lista de `excluded_events`.

## Handlers disponibles en Monolog

Monolog incluye numerosos handlers que puedes utilizar para enviar tus logs a diferentes destinos:

- **StreamHandler**: Escribe logs en archivos o streams (PHP streams)
- **RotatingFileHandler**: Rota los archivos de log automáticamente (diarios, semanales, etc.)
- **SlackWebhookHandler**: Envía logs a un canal de Slack vía webhook
- **TelegramBotHandler**: Envía logs a un bot de Telegram
- **FirePHPHandler**: Envía logs a FirePHP (útil para depuración)
- **ChromePHPHandler**: Envía logs a Chrome Logger
- **NativeMailerHandler**: Envía logs por email
- **SymfonyMailerHandler**: Envía logs usando Symfony Mailer
- **ElasticsearchHandler**: Almacena logs en Elasticsearch
- **RedisHandler**: Almacena logs en Redis
- **MongoDBHandler**: Almacena logs en MongoDB

Consulta la [documentación oficial de Monolog](https://github.com/Seldaek/monolog/blob/main/doc/02-handlers-formatters-processors.md) para más detalles sobre estos handlers y cómo configurarlos.

## Procesadores y formateadores

Además de los handlers, Monolog permite personalizar el formato de los logs y agregar información adicional mediante procesadores:

### Formateadores personalizados

```php
use Monolog\Formatter\LineFormatter;

// Formato personalizado
$format = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
$formatter = new LineFormatter($format);

// Aplicar el formateador a un handler
$handler = new StreamHandler(storagePath('logs/custom.log'));
$handler->setFormatter($formatter);

logger()->pushHandler($handler);
```

### Procesadores

Los procesadores te permiten agregar información adicional a todos los registros:

```php
use Monolog\Processor\WebProcessor;
use Monolog\Processor\IntrospectionProcessor;

// Agregar información web (IP, URL, etc.)
logger()->getLogger()->pushProcessor(new WebProcessor());

// Agregar información sobre desde dónde se llamó al logger
logger()->getLogger()->pushProcessor(new IntrospectionProcessor());
```

## Buenas prácticas para el logging

1. **Usa el nivel adecuado**: Usa diferentes niveles según la importancia del mensaje.
2. **Incluye contexto**: Agrega arreglos asociativos con información relevante.
3. **Mensajes claros**: Escribe mensajes descriptivos y coherentes.
4. **Evita información sensible**: No registres contraseñas, tokens, etc.
5. **Rota tus logs**: Usa RotatingFileHandler para evitar archivos demasiado grandes.
6. **Configura según el entorno**: Usa diferentes niveles y handlers dependiendo del entorno (dev, test, prod).

## Referencias

- [Documentación de Monolog](https://github.com/Seldaek/monolog/blob/main/README.md)
- [PSR-3: Interfaz Logger](https://www.php-fig.org/psr/psr-3/)
