<?php

namespace LightWeight\Tests\Integration\Http;

use LightWeight\Application;
use LightWeight\Config\Config;
use LightWeight\Container\Container;
use LightWeight\Http\Contracts\MiddlewareContract;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Contracts\ResponseContract;
use LightWeight\Http\HttpMethod;
use LightWeight\Http\Request;
use LightWeight\Http\Response;
use LightWeight\Routing\Route;
use LightWeight\Routing\Router;
use PHPUnit\Framework\TestCase;

/**
 * Este middleware tiene un contador que se incrementa cada vez que se ejecuta,
 * lo que nos permite verificar que se ejecuta correctamente y en el orden correcto.
 */
class CounterMiddleware implements MiddlewareContract
{
    public static int $counter = 0;
    private int $priority;

    public function __construct(int $priority = 0)
    {
        $this->priority = $priority;
    }

    public function handle(RequestContract $request, \Closure $next)
    {
        self::$counter++;
        // Guardar el valor actual del contador para este middleware
        $myCounter = self::$counter;
        
        // Obtener el orden actual o un array vacío si no existe
        $currentOrder = $request->getAttribute('middleware_order', []);
        
        // Añadir nuestra prioridad al final del array
        $currentOrder[] = $this->priority;
        
        // Guardar el array actualizado
        $request->addAttribute('middleware_order', $currentOrder);
        
        $response = $next($request);
        
        // También podemos modificar la respuesta
        $response->setHeader('X-Counter-' . $this->priority, (string)$myCounter);
        
        return $response;
    }

    public static function reset(): void
    {
        self::$counter = 0;
    }
}

class AppIntegrationTest extends TestCase
{
    private static string $originalAppRoot;
    private Application $app;
    private Router $router;

    public static function setUpBeforeClass(): void
    {
        // Guardar la raíz original de la aplicación
        self::$originalAppRoot = Application::$root ?? '';
        Application::$root = __DIR__ . '/../../..';
    }

    public static function tearDownAfterClass(): void
    {
        // Restaurar la raíz original de la aplicación
        if (self::$originalAppRoot) {
            Application::$root = self::$originalAppRoot;
        }
    }

    protected function setUp(): void
    {
        // Resetear el contador
        CounterMiddleware::reset();
        Container::getInstance();
        
        // Crear una nueva instancia de App con componentes reales (no mocks)
        $this->app = new Application();
        
        // Configurar el Router real
        $this->router = new Router();
        $this->app->router = $this->router;
        
        // Indicar al contenedor de dependencias que usamos este router
        app(Application::class)->bind(Router::class, fn(\DI\Container $c) => $this->router);
        
        // Registrar la implementación de RequestContract
        singleton(RequestContract::class, Request::class);
        
        // Configurar grupos de middleware
        $this->router->setMiddlewareGroups([
            'count' => [
                \LightWeight\Tests\Integration\Http\CounterMiddleware::class,
            ],
            'priority' => [
                \LightWeight\Tests\Integration\Http\CounterMiddleware::class,
            ]
        ]);
        
        // Cargar configuración básica
        Config::$config = [
            'app' => [
                'debug' => true,
            ],
        ];
    }

    protected function tearDown(): void
    {
        CounterMiddleware::reset();
        Container::deleteInstance();
    }

    /**
     * Helper para crear una solicitud para testing
     */
    private function createRequest(string $uri, string $method = 'GET'): RequestContract
    {
        $request = Container::get(RequestContract::class);
        $request->setUri($uri)
                ->setMethod(HttpMethod::from($method));
        return $request;
    }

    /**
     * Test que verifica que un middleware global se ejecuta correctamente
     */
    public function testGlobalMiddleware(): void
    {
        // Configurar middleware global
        $this->router->setGlobalMiddlewares([
            \LightWeight\Tests\Integration\Http\CounterMiddleware::class,
        ]);
        
        // Registrar una ruta simple
        $this->router->get('/test', function () {
            return Response::text('OK');
        });
        
        // Crear la solicitud
        $request = $this->createRequest('/test');
        $this->app->request = $request;
        
        // Ejecutar la aplicación manualmente
        $response = $this->router->resolve($request);
        
        // Verificar que el middleware se ejecutó
        $this->assertEquals(1, CounterMiddleware::$counter);
        $this->assertEquals('1', $response->headers('X-Counter-0'));
        $this->assertEquals('OK', $response->getContent());
    }

    /**
     * Test que verifica que los middleware de ruta se ejecutan correctamente
     */
    public function testRouteMiddleware(): void
    {
        // Registrar una ruta con middleware
        $route = $this->router->get('/test-route', function () {
            return Response::text('Route OK');
        });
        $route->setMiddlewares([
            \LightWeight\Tests\Integration\Http\CounterMiddleware::class,
        ]);
        
        // Crear la solicitud
        $request = $this->createRequest('/test-route');
        $this->app->request = $request;
        
        // Ejecutar la aplicación manualmente
        $response = $this->router->resolve($request);
        
        // Verificar que el middleware se ejecutó
        $this->assertEquals(1, CounterMiddleware::$counter);
        $this->assertEquals('1', $response->headers('X-Counter-0'));
        $this->assertEquals('Route OK', $response->getContent());
    }

    /**
     * Test que verifica que los middleware de grupo se ejecutan correctamente
     */
    public function testGroupMiddleware(): void
    {
        // Registrar una ruta con middleware de grupo
        $route = $this->router->get('/test-group', function () {
            return Response::text('Group OK');
        });
        $route->setMiddlewareGroups(['count']);
        
        // Crear la solicitud
        $request = $this->createRequest('/test-group');
        $this->app->request = $request;
        
        // Ejecutar la aplicación manualmente
        $response = $this->router->resolve($request);
        
        // Verificar que el middleware se ejecutó
        $this->assertEquals(1, CounterMiddleware::$counter);
        $this->assertEquals('1', $response->headers('X-Counter-0'));
        $this->assertEquals('Group OK', $response->getContent());
    }

    /**
     * Test que verifica que todos los tipos de middleware se ejecutan en el orden correcto
     */
    public function testMiddlewareOrder(): void
    {
        // Configurar middleware global con prioridad 1
        $this->router->setGlobalMiddlewares([
            \LightWeight\Tests\Integration\Http\CounterMiddleware::class,
        ]);
        
        // Registrar una ruta con middleware y grupo de middleware
        $route = $this->router->get('/test-all', function (Request $request) {
            // Obtener el orden de ejecución de los middleware
            $order = $request->getAttribute('middleware_order', []);
            
            return Response::json([
                'message' => 'All OK',
                'middleware_order' => $order
            ]);
        });
        
        // Middleware de ruta con prioridad 2
        $route->setMiddlewares([
            new CounterMiddleware(2),
        ]);
        
        // Middleware de grupo con prioridad 3
        $this->router->setMiddlewareGroups([
            'count' => [
                new CounterMiddleware(3),
            ]
        ]);
        $route->setMiddlewareGroups(['count']);
        
        // Crear la solicitud
        $request = $this->createRequest('/test-all');
        $this->app->request = $request;
        
        // Ejecutar la aplicación manualmente
        $response = $this->router->resolve($request);
        $content = json_decode($response->getContent(), true);
        
        // Verificar que todos los middleware se ejecutaron (3 en total)
        $this->assertEquals(3, CounterMiddleware::$counter);
        
        // Verificar los headers que los middleware agregaron
        // El valor de cada X-Counter-N es el valor del contador en el momento en que 
        // ese middleware particular se ejecutó
        $this->assertEquals('1', $response->headers('X-Counter-0')); // Middleware global (ejecutado primero)
        $this->assertEquals('2', $response->headers('X-Counter-2')); // Middleware de grupo (ejecutado segundo)
        $this->assertEquals('3', $response->headers('X-Counter-3')); // Middleware de ruta (ejecutado tercero)

        // Verificar que el orden de ejecución coincide con lo esperado:
        // primero global, luego grupo, luego ruta
        $this->assertEquals([0, 2, 3], $content['middleware_order']);
    }

    /**
     * Test que verifica que un middleware puede terminar temprano la ejecución
     */
    public function testMiddlewareEarlyTermination(): void
    {
        // Crear un middleware que termina temprano
        $earlyTerminationMiddleware = new class implements MiddlewareContract {
            public function handle(RequestContract $request, \Closure $next)
            {
                // Terminar temprano, sin llamar a $next
                return Response::text('Early termination')->setStatus(403);
            }
        };
        
        // Registrar una ruta con el middleware
        $route = $this->router->get('/test-early', function () {
            return Response::text('This should not be reached');
        });
        $route->setMiddlewares([$earlyTerminationMiddleware]);
        
        // También agregamos un middleware contador para verificar que no se ejecuta
        $this->router->setGlobalMiddlewares([
            \LightWeight\Tests\Integration\Http\CounterMiddleware::class,
        ]);
        
        // Crear la solicitud
        $request = $this->createRequest('/test-early');
        $this->app->request = $request;
        
        // Ejecutar la aplicación manualmente
        $response = $this->router->resolve($request);
        
        // Verificar que la respuesta es del middleware, no de la ruta
        $this->assertEquals('Early termination', $response->getContent());
        $this->assertEquals(403, $response->getStatus());
        
        // Verificar que nuestro contador middleware no se ejecutó después del middleware de terminación temprana
        $this->assertEquals(1, CounterMiddleware::$counter);
    }
}
