<?php

namespace LightWeight\Tests\Events;

use App\Events\Listeners\SendWelcomeEmailListener;
use App\Services\EmailService;
use LightWeight\Container\Container;
use LightWeight\Events\GenericEvent;
use LightWeight\Events\EventDispatcher;
use LightWeight\Events\Contracts\EventDispatcherInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Test para verificar la inyección de dependencias en listeners de eventos
 */
class ListenerDependencyInjectionTest extends TestCase
{
    private EventDispatcher $eventDispatcher;
    private EmailService $emailService;
    
    protected function setUp(): void
    {
        // Limpiar contenedor entre pruebas
        Container::deleteInstance();
        Container::getInstance();
        
        // Configurar servicios en el contenedor
        $this->emailService = new EmailService(true); // Modo prueba activado
        Container::set(EmailService::class, $this->emailService);
        
        // Configurar dispatcher de eventos
        $this->eventDispatcher = new EventDispatcher();
        Container::set(EventDispatcherInterface::class, $this->eventDispatcher);
    }
    
    protected function tearDown(): void
    {
        Container::deleteInstance();
    }
    
    /**
     * Test que verifica que las dependencias se inyectan correctamente en un listener
     */
    public function testListenerReceivesDependenciesThroughInjection(): void
    {
        // Registrar el listener usando la clase (que debería ser instanciada por el contenedor)
        $this->eventDispatcher->listen('user.registered', SendWelcomeEmailListener::class);
        
        // Crear un usuario de prueba
        $user = new stdClass();
        $user->name = 'John Doe';
        $user->email = 'john@example.com';
        
        // Crear un evento con los datos del usuario
        $event = new GenericEvent('user.registered', ['user' => $user]);
        
        // Disparar el evento
        $this->eventDispatcher->dispatch($event);
        
        // Verificar que el email se envió usando el servicio de email
        $sentEmail = $this->emailService->getLastSentEmail();
        
        // Comprobar que el email fue enviado correctamente
        $this->assertNotNull($sentEmail);
        $this->assertEquals('john@example.com', $sentEmail['to']);
        $this->assertEquals('Bienvenido a nuestra plataforma', $sentEmail['subject']);
        $this->assertEquals('emails.welcome', $sentEmail['template']);
        $this->assertEquals(['userName' => 'John Doe'], $sentEmail['data']);
    }
    
    /**
     * Test que verifica que las dependencias se inyectan correctamente cuando
     * el listener es registrado a través de un proveedor de servicios
     */
    public function testListenersDependenciesWhenRegisteredViaServiceProvider(): void
    {
        // Simular un AppEventServiceProvider que registra el listener a través de una propiedad $listen
        $listen = [
            'user.registered' => [
                SendWelcomeEmailListener::class
            ]
        ];
        
        // Registrar los listeners manualmente (como lo haría un service provider)
        foreach ($listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                // Importante: Container::make instancia la clase utilizando el contenedor
                // y resuelve las dependencias automáticamente
                $this->eventDispatcher->listen($event, function ($event) use ($listener) {
                    $listenerInstance = Container::make($listener);
                    $listenerInstance->handle($event);
                });
            }
        }
        
        // Crear un usuario de prueba
        $user = new stdClass();
        $user->name = 'Jane Smith';
        $user->email = 'jane@example.com';
        
        // Disparar el evento
        $this->eventDispatcher->dispatch('user.registered', ['user' => $user]);
        
        // Verificar que el email se envió
        $sentEmail = $this->emailService->getLastSentEmail();
        
        // Comprobar que el email fue enviado correctamente
        $this->assertNotNull($sentEmail);
        $this->assertEquals('jane@example.com', $sentEmail['to']);
        $this->assertEquals('Bienvenido a nuestra plataforma', $sentEmail['subject']);
    }
}
