<?php

namespace LightWeight\Tests\Events;

use LightWeight\Container\Container;
use LightWeight\Events\GenericEvent;
use LightWeight\Events\EventDispatcher;
use LightWeight\Events\Contracts\EventDispatcherContract;
use LightWeight\Events\Contracts\EventContract;
use LightWeight\Events\Contracts\ListenerContract;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Clase de servicio de email para pruebas
 */
class TestEmailService
{
    private bool $testMode;
    private array $lastSentEmail = [];

    public function __construct(bool $testMode = false)
    {
        $this->testMode = $testMode;
    }

    public function sendWelcomeEmail(string $to, string $name): bool
    {
        $emailData = [
            'to' => $to,
            'subject' => 'Bienvenido a nuestra plataforma',
            'template' => 'emails.welcome',
            'data' => ['userName' => $name]
        ];

        if ($this->testMode) {
            $this->lastSentEmail = $emailData;
            return true;
        }

        // En un entorno real, aquí enviaríamos el email
        return true;
    }

    public function getLastSentEmail(): array
    {
        return $this->lastSentEmail;
    }
}

/**
 * Listener para eventos de registro de usuario
 */
class TestWelcomeEmailListener implements ListenerContract
{
    private TestEmailService $emailService;

    public function __construct(TestEmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function handle(EventContract $event): void
    {
        // Obtener datos del usuario desde el evento
        $userData = $event->getData();
        if (isset($userData['user'])) {
            $user = $userData['user'];
            if (isset($user->email, $user->name)) {
                $this->emailService->sendWelcomeEmail($user->email, $user->name);
            }
        }
    }
}

/**
 * Test para verificar la inyección de dependencias en listeners de eventos
 */
class ListenerDependencyInjectionTest extends TestCase
{
    private EventDispatcher $eventDispatcher;
    private TestEmailService $emailService;

    protected function setUp(): void
    {
        // Limpiar contenedor entre pruebas
        Container::deleteInstance();
        Container::getInstance();

        // Configurar servicios en el contenedor
        $this->emailService = new TestEmailService(true); // Modo prueba activado
        Container::set(TestEmailService::class, $this->emailService);

        // Configurar dispatcher de eventos
        $this->eventDispatcher = new EventDispatcher();
        Container::set(EventDispatcherContract::class, $this->eventDispatcher);
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
        $this->eventDispatcher->listen('user.registered', TestWelcomeEmailListener::class);

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
                TestWelcomeEmailListener::class
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
