<?php

namespace Tests\Routing;

use LightWeight\Container\Container;
use LightWeight\Events\Contracts\EventDispatcherInterface;
use LightWeight\Events\EventDispatcher;
use LightWeight\Events\System\RouterMatched;
use LightWeight\Http\HttpMethod;
use LightWeight\Http\Request;
use LightWeight\Routing\Router;
use PHPUnit\Framework\TestCase;

class RouterMatchedEventTest extends TestCase
{
    protected Router $router;
    protected Request $request;
    protected EventDispatcher $eventDispatcher;
    protected bool $eventFired = false;
    protected array $eventData = [];

    protected function setUp(): void
    {
        // Crear un contenedor limpio para aislar las pruebas
        Container::deleteInstance();
        $container = Container::getInstance();

        // Registrar el dispatcher de eventos
        $this->eventDispatcher = new EventDispatcher();
        $container->set(EventDispatcherInterface::class, $this->eventDispatcher);
        
        // Configurar la aplicación para que tenga una propiedad 'events'
        $app = new \LightWeight\App();
        $app->events = $this->eventDispatcher;
        $container->set(\LightWeight\App::class, $app);

        // Configurar el router
        $this->router = new Router();
        
        // Configurar un listener para el evento
        $this->eventDispatcher->listen('router.matched', function ($event) {
            $this->eventFired = true;
            $this->eventData = [
                'uri' => $event->getUri(),
                'method' => $event->getMethod(),
                'route' => $event->getRoute()
            ];
        });
        
        // Crear una solicitud de prueba
        $this->request = new Request(HttpMethod::GET, '/test', [], [], []);
    }

    public function testRouterMatchedEventIsFired(): void
    {
        // Crear una ruta que coincida con la solicitud
        $this->router->get('/test', function () {
            return 'Test Route';
        });
        
        // Resolver la ruta
        $route = $this->router->resolveRoute($this->request);
        
        // Verificar que el evento se disparó
        $this->assertTrue($this->eventFired, 'El evento router.matched no fue disparado');
        
        // Verificar que los datos del evento son correctos
        $this->assertEquals('/test', $this->eventData['uri']);
        $this->assertEquals('GET', $this->eventData['method']);
        $this->assertSame($route, $this->eventData['route']);
    }
    
    public function testRouterMatchedEventContainsCorrectData(): void
    {
        // Crear una ruta con parámetros
        $this->router->get('/users/{id}', function ($id) {
            return "User $id";
        });
        
        // Crear una solicitud para esa ruta
        $userRequest = new Request(HttpMethod::GET, '/users/123', [], [], []);
        
        // Resolver la ruta
        $route = $this->router->resolveRoute($userRequest);
        
        // Verificar que el evento se disparó con los datos correctos
        $this->assertTrue($this->eventFired);
        $this->assertEquals('/users/123', $this->eventData['uri']);
        $this->assertEquals('GET', $this->eventData['method']);
        $this->assertSame($route, $this->eventData['route']);
    }
}
