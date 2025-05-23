<?php

namespace LightWeight\Tests\Events;

use LightWeight\Events\Contracts\EventContract;
use LightWeight\Events\Contracts\ListenerContract;
use LightWeight\Events\Event;
use LightWeight\Events\EventDispatcher;
use LightWeight\Events\GenericEvent;
use PHPUnit\Framework\TestCase;

class EventDispatcherTest extends TestCase
{
    private EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
    }

    public function testCanRegisterAndCheckListeners(): void
    {
        // Inicialmente no hay listeners
        $this->assertFalse($this->dispatcher->hasListeners('test.event'));

        // Registramos un listener
        $this->dispatcher->listen('test.event', function (): void {});

        // Ahora debería existir
        $this->assertTrue($this->dispatcher->hasListeners('test.event'));
    }

    public function testCanDispatchStringEventAndExecuteListener(): void
    {
        $wasCalled = false;
        $receivedData = null;

        // Registramos un listener
        $this->dispatcher->listen('test.event', function (EventContract $event) use (&$wasCalled, &$receivedData) {
            $wasCalled = true;
            $receivedData = $event->getData();
        });

        // Disparamos el evento
        $this->dispatcher->dispatch('test.event', ['key' => 'value']);

        // Verificamos que se ejecutó y recibió los datos correctos
        $this->assertTrue($wasCalled);
        $this->assertEquals(['key' => 'value'], $receivedData);
    }

    public function testCanDispatchEventObjectAndExecuteListener(): void
    {
        $wasCalled = false;
        $receivedData = null;

        // Creamos un evento de prueba
        $testEvent = new class (['test' => 123]) extends Event {
            public function getName(): string
            {
                return 'custom.event';
            }
        };

        // Registramos un listener
        $this->dispatcher->listen('custom.event', function (EventContract $event) use (&$wasCalled, &$receivedData) {
            $wasCalled = true;
            $receivedData = $event->getData();
        });

        // Disparamos el evento
        $this->dispatcher->dispatch($testEvent);

        // Verificamos que se ejecutó y recibió los datos correctos
        $this->assertTrue($wasCalled);
        $this->assertEquals(['test' => 123], $receivedData);
    }

    public function testCanRegisterAndExecuteListenerClass(): void
    {
        // Creamos una clase listener
        $listener = new class () implements ListenerContract {
            public $wasCalled = false;
            public $eventData = null;

            public function handle(EventContract $event): void
            {
                $this->wasCalled = true;
                $this->eventData = $event->getData();
            }
        };

        // Registramos el listener
        $this->dispatcher->listen('test.event', $listener);

        // Disparamos el evento
        $this->dispatcher->dispatch('test.event', ['foo' => 'bar']);

        // Verificamos
        $this->assertTrue($listener->wasCalled);
        $this->assertEquals(['foo' => 'bar'], $listener->eventData);
    }

    public function testCanExecuteMultipleListeners(): void
    {
        $callCount = 0;

        // Registramos múltiples listeners
        $this->dispatcher->listen('multi.event', function () use (&$callCount) {
            $callCount++;
        });

        $this->dispatcher->listen('multi.event', function () use (&$callCount) {
            $callCount++;
        });

        $this->dispatcher->listen('multi.event', function () use (&$callCount) {
            $callCount++;
        });

        // Disparamos el evento
        $this->dispatcher->dispatch('multi.event');

        // Verificamos que todos se ejecutaron
        $this->assertEquals(3, $callCount);
    }

    public function testCanForgetAllListenersForEvent(): void
    {
        // Registramos un listener
        $this->dispatcher->listen('test.event', function () {});

        // Confirmamos que existe
        $this->assertTrue($this->dispatcher->hasListeners('test.event'));

        // Lo eliminamos
        $this->dispatcher->forget('test.event');

        // Verificamos que ya no existe
        $this->assertFalse($this->dispatcher->hasListeners('test.event'));
    }

    public function testCanForgetAllListeners(): void
    {
        // Registramos varios listeners en diferentes eventos
        $this->dispatcher->listen('event1', function () {});
        $this->dispatcher->listen('event2', function () {});

        // Confirmamos que existen
        $this->assertTrue($this->dispatcher->hasListeners('event1'));
        $this->assertTrue($this->dispatcher->hasListeners('event2'));

        // Eliminamos todos
        $this->dispatcher->forget();

        // Verificamos que ya no existen
        $this->assertFalse($this->dispatcher->hasListeners('event1'));
        $this->assertFalse($this->dispatcher->hasListeners('event2'));
    }

    public function testGenericEventHoldsNameAndData(): void
    {
        $event = new GenericEvent('test.name', ['data1' => 'value1']);

        $this->assertEquals('test.name', $event->getName());
        $this->assertEquals(['data1' => 'value1'], $event->getData());
    }
}
