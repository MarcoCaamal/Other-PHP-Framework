<?php

namespace Tests\Events;

use LightWeight\Events\Contracts\EventDispatcherContract;
use LightWeight\Events\Contracts\EventSubscriberContract;
use LightWeight\Events\EventDispatcher;
use LightWeight\Events\GenericEvent;
use LightWeight\Events\Subscribers\ExampleSubscriber;
use PHPUnit\Framework\TestCase;

class EventSubscriberTest extends TestCase
{
    protected EventDispatcherContract $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatcher = new EventDispatcher();
    }

    public function testExampleSubscriber()
    {
        // Crear un mock del suscriptor para verificar que se llaman sus métodos
        $subscriber = $this->createPartialMock(ExampleSubscriber::class, ['onApplicationBootstrapped', 'onApplicationTerminating']);

        // Configurar expectativas
        $subscriber->expects($this->once())
            ->method('onApplicationBootstrapped')
            ->with($this->isInstanceOf(GenericEvent::class));

        $subscriber->expects($this->once())
            ->method('onApplicationTerminating')
            ->with($this->isInstanceOf(GenericEvent::class));

        // Registrar el suscriptor
        $subscriber->subscribe($this->dispatcher);

        // Disparar eventos
        $this->dispatcher->dispatch('application.bootstrapped');
        $this->dispatcher->dispatch('application.terminating');
    }

    public function testSubscriberInterface()
    {
        $subscriber = new ExampleSubscriber();
        $this->assertInstanceOf(EventSubscriberContract::class, $subscriber);
    }
}
