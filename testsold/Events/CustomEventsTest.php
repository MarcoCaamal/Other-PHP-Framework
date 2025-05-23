<?php

namespace LightWeight\Tests\Events;

use LightWeight\Events\Event;
use LightWeight\Events\EventDispatcher;
use PHPUnit\Framework\TestCase;

class CustomEventsTest extends TestCase
{
    private EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
    }

    public function testCustomEventClass(): void
    {
        // Crear una clase de evento personalizada
        $testEvent = new class (['id' => 1, 'name' => 'Test User']) extends Event {
            public function getName(): string
            {
                return 'user.created';
            }

            public function getUserId(): int
            {
                return $this->data['id'] ?? 0;
            }

            public function getUserName(): string
            {
                return $this->data['name'] ?? '';
            }
        };

        $capturedEvent = null;

        // Registrar un listener
        $this->dispatcher->listen('user.created', function ($event) use (&$capturedEvent) {
            $capturedEvent = $event;
        });

        // Disparar el evento
        $this->dispatcher->dispatch($testEvent);

        // Verificar que el evento fue capturado
        $this->assertNotNull($capturedEvent);
        $this->assertEquals('user.created', $capturedEvent->getName());
        $this->assertEquals(1, $capturedEvent->getUserId());
        $this->assertEquals('Test User', $capturedEvent->getUserName());
    }

    public function testEventInheritance(): void
    {
        // Definimos una clase base para eventos relacionados con usuarios
        $userEvent = new class (['userId' => 123]) extends Event {
            public function getName(): string
            {
                return 'user.event';
            }

            public function getUserId(): int
            {
                return $this->data['userId'] ?? 0;
            }
        };

        // Evento específico que hereda del evento base
        $loginEvent = new class (['userId' => 123, 'timestamp' => 1000]) extends Event {
            public function getName(): string
            {
                return 'user.login';
            }

            public function getUserId(): int
            {
                return $this->data['userId'] ?? 0;
            }

            public function getTimestamp(): int
            {
                return $this->data['timestamp'] ?? 0;
            }
        };

        $baseEventTriggered = false;
        $loginEventTriggered = false;

        // Registrar listeners para ambos tipos de eventos
        $this->dispatcher->listen('user.event', function () use (&$baseEventTriggered) {
            $baseEventTriggered = true;
        });

        $this->dispatcher->listen('user.login', function () use (&$loginEventTriggered) {
            $loginEventTriggered = true;
        });

        // Disparar el evento login, que es más específico
        $this->dispatcher->dispatch($loginEvent);

        // Solo debe dispararse el listener específico, no el general
        $this->assertFalse($baseEventTriggered);
        $this->assertTrue($loginEventTriggered);
    }

    public function testEventPriority(): void
    {
        $executionOrder = [];

        // Registrar varios listeners en un orden específico
        $this->dispatcher->listen('priority.test', function () use (&$executionOrder) {
            $executionOrder[] = 'first';
        });

        $this->dispatcher->listen('priority.test', function () use (&$executionOrder) {
            $executionOrder[] = 'second';
        });

        $this->dispatcher->listen('priority.test', function () use (&$executionOrder) {
            $executionOrder[] = 'third';
        });

        // Disparar el evento
        $this->dispatcher->dispatch('priority.test');

        // Verificar que la ejecución ocurrió en el orden de registro
        $this->assertEquals(['first', 'second', 'third'], $executionOrder);
    }
}
