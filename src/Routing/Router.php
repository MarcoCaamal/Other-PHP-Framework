<?php

namespace LightWeight\Routing;

use LightWeight\Container\Container;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Contracts\ResponseContract;
use LightWeight\Http\HttpMethod;
use LightWeight\Http\HttpNotFoundException;
use LightWeight\Routing\Exceptions\RouteDuplicatedNameException;
use LightWeight\Routing\Route;

/**
 * HTTP Router
 */
class Router
{
    /**
     * HTTP routes
     * @var array<string, Route[]>
     */
    protected array $routes = [];
    /**
     * Global HTTP middlewares.
     *
     * @var array<class-string<\LightWeight\Http\Contracts\MiddlewareContract>>
     */
    protected array $globalMiddlewares = [];

    /**
     * Middleware groups.
     *
     * @var array<string, array<class-string<\LightWeight\Http\Contracts\MiddlewareContract>>>
     */
    protected array $middlewareGroups = [];

    /**
     * Create a new router
     */    public function __construct(protected Container $container)
    {
        foreach (HttpMethod::cases() as $method) {
            $this->routes[$method->value] = [];
        }
    }
    /**
     * Register a new route with the given `#method` and `$uri`
     *
     * @param \LightWeight\Http\HttpMethod $method
     * @param string $uri
     * @param \Closure $action
     * @return Route
     */
    protected function registerRoute(HttpMethod $method, string $uri, \Closure|array $action): Route
    {
        $uriWithPrefix = rtrim(Route::$prefix ?? '', '/') . '/' . ltrim($uri, '/');
        $uriWithPrefix = '/' . trim($uriWithPrefix, '/');
        $route = new Route($uriWithPrefix, $action);
        $this->routes[$method->value][] = $route;
        return $route;
    }
    /**
     * Resolve the route of the `$request`
     *
     * @param \LightWeight\Http\Contracts\RequestContract $request
     * @throws \LightWeight\Http\HttpNotFoundException
     * @return \LightWeight\Routing\Route
     */
    public function resolveRoute(RequestContract $request): Route
    {
        foreach ($this->routes[$request->method()->value] as $route) {
            if ($route->matches($request->uri())) {                // Disparar el evento router.matched cuando se encuentra una ruta
                try {
                    // Intentar obtener el despachador de eventos directamente del contenedor
                    if ($this->container->has(\LightWeight\Events\Contracts\EventDispatcherContract::class)) {
                        $eventDispatcher = $this->container->get(\LightWeight\Events\Contracts\EventDispatcherContract::class);
                        $eventDispatcher->dispatch(new \LightWeight\Events\System\RouterMatched([
                            'route' => $route,
                            'uri' => $request->uri(),
                            'method' => $request->method()->value
                        ]));
                    }
                } catch (\Throwable $e) {
                    // Silenciar errores de eventos para no interrumpir el enrutamiento
                    // Idealmente, esto debería registrarse en caso de error
                }
                return $route;
            }
        }
        throw new HttpNotFoundException();
    }

    /**
     * Set global middlewares for all routes.
     *
     * @param array<class-string<\LightWeight\Http\Contracts\MiddlewareContract>> $middlewares
     * @return void
     */
    public function setGlobalMiddlewares(array $middlewares): void
    {
        $this->globalMiddlewares = $middlewares;
    }

    /**
     * Get global middlewares.
     *
     * @return array<class-string<\LightWeight\Http\Contracts\MiddlewareContract>>
     */
    public function getGlobalMiddlewares(): array
    {
        return $this->globalMiddlewares;
    }

    /**
     * Set middleware groups.
     *
     * @param array<string, array<class-string<\LightWeight\Http\Contracts\MiddlewareContract>>> $groups
     * @return void
     */
    public function setMiddlewareGroups(array $groups): void
    {
        $this->middlewareGroups = $groups;
    }

    /**
     * Get middleware groups.
     *
     * @return array<string, array<class-string<\LightWeight\Http\Contracts\MiddlewareContract>>>
     */
    public function getMiddlewareGroups(): array
    {
        return $this->middlewareGroups;
    }

    /**
     * Get middlewares from a group.
     *
     * @param string $group
     * @return array<class-string<\LightWeight\Http\Contracts\MiddlewareContract>>
     */
    public function getMiddlewareGroup(string $group): array
    {
        return $this->middlewareGroups[$group] ?? [];
    }

    /**
     * Check if the router has any defined routes
     * 
     * @return bool
     */
    public function isEmpty(): bool
    {
        foreach ($this->routes as $methodRoutes) {
            if (!empty($methodRoutes)) {
                return false;
            }
        }
        
        return true;
    }

    public function resolve(RequestContract $request): ResponseContract
    {
        $route = $this->resolveRoute($request);
        $request->setRoute($route);
        $action = $route->action();        // Recopilar todos los middlewares aplicables
        $middlewareInstances = array_map(
            function($mw) {
                if (is_string($mw)) {
                    return $this->container->make($mw); // Usar el contenedor inyectado para aprovechar la inyección de dependencias
                }
                return $mw;
            }, 
            $this->globalMiddlewares
        );

        // Añadir los middlewares de la ruta
        $middlewareInstances = array_merge($middlewareInstances, $route->middlewares());

        // Si hay middleware groups definidos en la ruta, añadirlos también
        if (method_exists($route, 'middlewareGroups') && !empty($route->middlewareGroups())) {
            foreach ($route->middlewareGroups() as $groupName) {                $groupMiddlewares = array_map(
                    function($mw) {
                        if (is_string($mw)) {
                            return $this->container->make($mw); // Usar el contenedor inyectado para aprovechar la inyección de dependencias
                        }
                        return $mw;
                    },
                    $this->getMiddlewareGroup($groupName)
                );
                $middlewareInstances = array_merge($middlewareInstances, $groupMiddlewares);
            }
        }        if(is_array($action)) {
            // Reemplazar el singleton helper con el container directamente
            if (!$this->container->has($action[0])) {
                $this->container->set($action[0], \DI\autowire($action[0]));
            }
            $controller = $this->container->get($action[0]);
            $action[0] = $controller;
            $middlewareInstances = array_merge($middlewareInstances, $controller->middlewares());
        }

        if (!empty($middlewareInstances)) {            return $this->runMiddlewares(
                $request,
                $middlewareInstances,
                function () use ($action, $request) {
                    // Usa PHP-DI call() directamente para invocar el controlador o función de cierre
                    if (is_array($action)) {
                        // Es un método de controlador [ControllerClass, 'method']
                        return $this->container->call([$action[0], $action[1]], [
                            'request' => $request,
                            ...$request->routeParameters()
                        ]);
                    } else {
                        // Es una función de cierre
                        return $this->container->call($action, [
                            'request' => $request,
                            ...$request->routeParameters()
                        ]);
                    }
                }
            );
        }
          // Sin middlewares, usa call directamente
        if (is_array($action)) {
            return $this->container->call([$action[0], $action[1]], [
                'request' => $request,
                ...$request->routeParameters()
            ]);
        } else {
            return $this->container->call($action, [
                'request' => $request,
                ...$request->routeParameters()
            ]);
        }
    }
    protected function runMiddlewares(RequestContract $request, array $middlewares, \Closure $target): ResponseContract
    {
        if (count($middlewares) === 0) {
            return $target();
        }

        return $middlewares[0]->handle(
            $request,
            fn ($request) => $this->runMiddlewares($request, array_slice($middlewares, 1), $target)
        );
    }
    protected function verifyIfExistsRouteWithDuplicatedName(Route $newRoute)
    {
        foreach($this->routes as $method) {
            foreach($method as $route) {
                if($route->name() === $newRoute->name() && $newRoute->name() !== null) {
                    throw new RouteDuplicatedNameException($newRoute->name() ?? '');
                }
            }
        }
    }
    /**
     * Register a GET route with the give `$uri` and `$action`
     *
     * @param string $uri
     * @param \Closure $action
     * @return Route
     */
    public function get(string $uri, \Closure|array $action): Route
    {
        return $this->registerRoute(HttpMethod::GET, $uri, $action);
    }

    /**
     * Register a POST route with the given `$uri` and `$action`
     *
     * @param string $uri
     * @param \Closure $action
     * @return Route
     */
    public function post(string $uri, \Closure|array $action): Route
    {
        return $this->registerRoute(HttpMethod::POST, $uri, $action);
    }

    /**
     * Register a PUT route with the given `$uri` and `$action`
     *
     * @param string $uri
     * @param \Closure $action
     * @return Route
     */
    public function put(string $uri, \Closure|array $action): Route
    {
        return $this->registerRoute(HttpMethod::PUT, $uri, $action);
    }

    /**
     * Register a DELETE route with the given `$uri` and `$action`
     *
     * @param string $uri
     * @param \Closure $action
     * @return Route
     */
    public function delete(string $uri, \Closure|array $action): Route
    {
        return $this->registerRoute(HttpMethod::DELETE, $uri, $action);
    }

    /**
     * Register a PATCH route with the given `$uri` and `$action`
     *
     * @param string $uri
     * @param \Closure $action
     * @return Route
     */
    public function patch(string $uri, \Closure|array $action): Route
    {
        return $this->registerRoute(HttpMethod::PATCH, $uri, $action);
    }
}
