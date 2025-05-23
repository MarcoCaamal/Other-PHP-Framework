<?php

namespace Tests\Events;

use LightWeight\Application;
use LightWeight\Config\Config;
use LightWeight\Events\Contracts\EventContract;
use LightWeight\Events\Contracts\EventDispatcherContract;
use LightWeight\Events\EventDispatcher;
use LightWeight\Log\Contracts\LoggerContract;
use LightWeight\Log\Handlers\EventLogHandler;
use LightWeight\Log\Logger;
use PHPUnit\Framework\TestCase;
use Monolog\Handler\TestHandler;
use Monolog\Level;

/**
 * Test para el sistema de registro de eventos
 */
class EventLoggingTest extends TestCase
{
    protected EventDispatcher $dispatcher;
    protected TestHandler $testHandler;
    protected Logger $logger;

    protected function setUp(): void
    {
        parent::setUp();

        // Inicialización de la aplicación
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__, 2));
        }

        // Inicializar las propiedades estáticas necesarias
        Application::$root = BASE_PATH;

        // Inicializar el dispatcher
        $this->dispatcher = new EventDispatcher();

        // Configurar el logger con un test handler para capturar los logs
        $this->testHandler = new TestHandler(Level::Debug);
        $this->logger = new Logger('test');
        $this->logger->pushHandler($this->testHandler);

        // Establecer configuración de eventos
        Config::$config['logging'] = [
            'event_logging' => [
                'enabled' => true,
                'excluded_events' => ['excluded.event'],
            ]
        ];
    }

    /**
     * Test que verifica si los eventos regulares se están registrando
     */
    public function testBasicEventLogging(): void
    {
        // Configurar event logging
        $excludedEvents = Config::$config['logging']['event_logging']['excluded_events'] ?? [];
        $eventLogHandler = new EventLogHandler($excludedEvents);

        // Registrar un listener para todos los eventos
        $this->dispatcher->listen('*', function ($event, ?string $eventName = null) use ($eventLogHandler) {
            if ($eventName === null && is_string($event)) {
                $eventName = $event;
                $event = null;
            }

            $eventLogHandler->handleEvent($eventName, $event, $this->logger);
        });

        // Dispatchar un evento simple
        $this->dispatcher->dispatch('test.event');

        // Verificar que se ha registrado el evento (mostramos todos los registros para depurar)
        $records = $this->testHandler->getRecords();
        foreach ($records as $record) {
            echo "Level: " . $record->level->getName() . ", Message: " . $record->message . PHP_EOL;
        }

        // Verificar específicamente el registro del evento
        $this->assertTrue($this->testHandler->hasInfoThatContains('Event dispatched: test.event'));
    }

    /**
     * Test que verifica si los objetos de eventos se registran con sus datos
     */
    public function testEventObjectLogging(): void
    {
        // Crear una clase de evento para pruebas
        $testEvent = new class () implements EventContract {
            public function getData(): array
            {
                return ['test' => 'value', 'number' => 123];
            }
            public function getName(): string
            {
                return 'test.event.object';
            }
        };

        // Configurar event logging
        $excludedEvents = Config::$config['logging']['event_logging']['excluded_events'] ?? [];
        $eventLogHandler = new EventLogHandler($excludedEvents);

        // Registrar un listener para todos los eventos
        $this->dispatcher->listen('*', function ($event, ?string $eventName = null) use ($eventLogHandler) {
            if ($eventName === null && is_string($event)) {
                $eventName = $event;
                $event = null;
            }

            $eventLogHandler->handleEvent($eventName, $event, $this->logger);
        });

        // Dispatchar un evento con objeto
        $this->dispatcher->dispatch($testEvent);

        // Verificar que se ha registrado el evento
        $this->assertTrue($this->testHandler->hasInfoThatContains('Event dispatched: test.event.object'));

        // Obtener los registros para verificar el contexto
        $records = $this->testHandler->getRecords();
        $found = false;

        foreach ($records as $record) {
            if (strpos($record->message, 'Event dispatched: test.event.object') !== false) {
                // Verificar que el contexto contiene los datos esperados
                $this->assertArrayHasKey('data', $record->context);
                $this->assertArrayHasKey('test', $record->context['data']);
                $this->assertArrayHasKey('number', $record->context['data']);
                $this->assertEquals('value', $record->context['data']['test']);
                $this->assertEquals(123, $record->context['data']['number']);
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, "No se encontró un registro con los datos esperados en el contexto");
    }

    /**
     * Test que verifica que los eventos excluidos no se registran
     */
    public function testExcludedEventsNotLogged(): void
    {
        // Configurar event logging
        $excludedEvents = Config::$config['logging']['event_logging']['excluded_events'] ?? [];
        $eventLogHandler = new EventLogHandler($excludedEvents);

        // Registrar un listener para todos los eventos
        $this->dispatcher->listen('*', function ($event, ?string $eventName = null) use ($eventLogHandler) {
            if ($eventName === null && is_string($event)) {
                $eventName = $event;
                $event = null;
            }

            $eventLogHandler->handleEvent($eventName, $event, $this->logger);
        });

        // Dispatchar un evento excluido
        $this->dispatcher->dispatch('excluded.event');

        // Verificar que no se ha registrado el evento
        $this->assertFalse($this->testHandler->hasInfoThatContains('Event dispatched: excluded.event'));
    }

    /**
     * Test que verifica la integración con el ServiceProvider
     */
    public function testServiceProviderEventLogging(): void
    {
        // Crear un mock del EventDispatcherContract
        $mockDispatcher = $this->createMock(EventDispatcherContract::class);

        // El dispatcher debe recibir una llamada a listen con '*'
        $mockDispatcher->expects($this->once())
            ->method('listen')
            ->with(
                $this->equalTo('*'),
                $this->callback(function ($callback) {
                    // Es una función anónima, solo podemos verificar el tipo
                    return is_callable($callback);
                })
            );

        // Crear un mock del container
        $mockContainer = $this->getMockBuilder('DI\Container')
            ->disableOriginalConstructor()
            ->getMock();

        // El container debe devolver un logger cuando se solicite
        $mockContainer->expects($this->once())
            ->method('get')
            ->with(LoggerContract::class)
            ->willReturn($this->logger);

        // Crear una instancia del proveedor de servicios que vamos a probar
        $provider = new class () extends \LightWeight\Providers\EventServiceProvider {
            // Esta clase extiende el provider real para poder acceder al método protegido
            public function testConfigureEventLogging($dispatcher, $container): void
            {
                $this->configureEventLogging($dispatcher, $container);
            }
        };

        // Configurar para habilitar el log de eventos
        Config::$config['logging'] = [
            'event_logging' => [
                'enabled' => true,
                'excluded_events' => ['excluded.event'],
            ]
        ];

        // Ejecutar el método que estamos probando
        $provider->testConfigureEventLogging($mockDispatcher, $mockContainer);
    }

    /**
     * Test que verifica el manejo de excepciones durante el registro de eventos
     */
    public function testExceptionHandlingDuringEventLogging(): void
    {
        // Crear un evento que lanzará una excepción al intentar obtener sus datos
        $problematicEvent = new class () implements EventContract {
            public function getData(): array
            {
                throw new \Exception("Error al obtener datos del evento");
            }
            public function getName(): string
            {
                return 'problematic.event';
            }
        };

        // Configurar event logging
        $excludedEvents = Config::$config['logging']['event_logging']['excluded_events'] ?? [];
        $eventLogHandler = new EventLogHandler($excludedEvents);

        // Registrar un listener para todos los eventos
        $this->dispatcher->listen('*', function ($event, ?string $eventName = null) use ($eventLogHandler) {
            if ($eventName === null && is_string($event)) {
                $eventName = $event;
                $event = null;
            }

            $eventLogHandler->handleEvent($eventName, $event, $this->logger);
        });

        // Dispatchar el evento que causará problemas
        $this->dispatcher->dispatch($problematicEvent);

        // Verificar que se registró el error
        $this->assertTrue($this->testHandler->hasErrorThatContains('Error logging event'));
        $this->assertTrue($this->testHandler->hasErrorThatContains('problematic.event'));
    }
}
