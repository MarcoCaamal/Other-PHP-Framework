<?php

namespace LightWeight\Tests\Events;

use LightWeight\App;
use LightWeight\Events\Contracts\EventDispatcherInterface;
use LightWeight\Events\Contracts\EventInterface;
use LightWeight\Events\Contracts\ListenerInterface;
use LightWeight\Events\EventDispatcher;
use LightWeight\Events\System\ApplicationBootstrapped;
use LightWeight\Events\System\ApplicationTerminating;
use LightWeight\Http\Response;
use PHPUnit\Framework\TestCase;

class EventsIntegrationTest extends TestCase
{
    protected App $app;

    protected function setUp(): void
    {
        // Mock App class para probar la integración
        $this->app = $this->createMock(App::class);
        $this->app->events = singleton(EventDispatcherInterface::class, EventDispatcher::class);
        
        // Registrar la app en el contenedor para las funciones helper
        \LightWeight\Container\Container::getInstance()->set(App::class, $this->app);
    }

    public function testEventHelperFunction(): void
    {
        $wasCalled = false;
        $data = null;

        // Registrar un listener usando la función helper "on"
        on('test.helper.event', function (EventInterface $event) use (&$wasCalled, &$data) {
            $wasCalled = true;
            $data = $event->getData();
        });

        // Disparar usando la función helper "event"
        event('test.helper.event', ['test' => 'data']);

        // Verificar
        $this->assertTrue($wasCalled);
        $this->assertEquals(['test' => 'data'], $data);
    }

    public function testApplicationBootstrappedEvent(): void
    {
        $eventReceived = false;

        // Registrar listener para el evento de bootstrap
        $this->app->events->listen(
            'application.bootstrapped', 
            function (EventInterface $event) use (&$eventReceived) {
                $eventReceived = true;
                $this->assertInstanceOf(ApplicationBootstrapped::class, $event);
            }
        );

        // Disparar el evento
        $this->app->events->dispatch(new ApplicationBootstrapped());

        // Verificar
        $this->assertTrue($eventReceived);
    }

    public function testApplicationTerminatingEvent(): void
    {
        $eventReceived = false;
        $responseData = null;

        // Crear una respuesta de prueba
        $response = new Response();
        $response->setContent('Test Content');

        // Registrar listener para el evento de terminación
        $this->app->events->listen(
            'application.terminating', 
            function (EventInterface $event) use (&$eventReceived, &$responseData) {
                $eventReceived = true;
                $responseData = $event->getData()['response'] ?? null;
            }
        );

        // Disparar el evento
        $this->app->events->dispatch(new ApplicationTerminating(['response' => $response]));

        // Verificar
        $this->assertTrue($eventReceived);
        $this->assertSame($response, $responseData);
    }

    public function testListenerClassIntegration(): void
    {
        // Crear una clase listener
        $listener = new class implements ListenerInterface {
            public static $wasCalled = false;
            
            public function handle(EventInterface $event): void
            {
                self::$wasCalled = true;
            }
        };
        
        // Limpiar el estado estático
        $listener::$wasCalled = false;
        
        // Registrar el listener usando la app directamente
        $this->app->events->listen('test.with.class', $listener);
        
        // Disparar usando la función helper
        event('test.with.class');
        
        // Verificar
        $this->assertTrue($listener::$wasCalled);
    }

    public function testForgetListenersHelperFunction(): void
    {
        // Registrar algunos listeners
        on('event.to.forget', function () {});
        on('event.to.keep', function () {});
        
        // Verificar que están registrados
        $this->assertTrue($this->app->events->hasListeners('event.to.forget'));
        $this->assertTrue($this->app->events->hasListeners('event.to.keep'));
        
        // Olvidar uno específico
        forgetListeners('event.to.forget');
        
        // Verificar que solo se olvidó el correcto
        $this->assertFalse($this->app->events->hasListeners('event.to.forget'));
        $this->assertTrue($this->app->events->hasListeners('event.to.keep'));
    }
}
