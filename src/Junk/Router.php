<?php
namespace Junk;

use Junk\HttpMethod;
use Junk\HttpNotFoundException;
use Request;

class Router {
    protected $routes = [];
    public function __construct() {
        foreach(HttpMethod::cases() as $method) {
            $this->routes[$method->value] = [];
        }
    }

    protected function registerRoute(HttpMethod $method, string $uri, \Closure $action) {
        $this->routes[$method->value][] = new Route($uri, $action);
    }

    public function resolve(Request $request): Route {
        foreach($this->routes[$request->method()->value] as $route) {
            if($route->matches($request->uri())) {
                return $route;
            }
        }
        throw new HttpNotFoundException();
    }

    public function get(string $uri, \Closure $action) {
        $this->registerRoute(HttpMethod::GET, $uri, $action);
    }

    public function post(string $uri, \Closure $action) {
        $this->registerRoute(HttpMethod::POST, $uri, $action);
    }

    public function put(string $uri, \Closure $action) {
        $this->registerRoute(HttpMethod::PUT, $uri, $action);
    }

    public function delete(string $uri, \Closure $action) {
        $this->registerRoute(HttpMethod::DELETE, $uri, $action);
    }

    public function patch(string $uri, \Closure $action) {
        $this->registerRoute(HttpMethod::PATCH, $uri, $action);
    }
}