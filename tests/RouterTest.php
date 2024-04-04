<?php
namespace Junk\Tests;

use Junk\HttpMethod;
use PHPUnit\Framework\TestCase;

use Junk\Router;

class RouterTest extends TestCase {
    public function testResolveBasicRouteWithCallback() {
        $uri = '/test';
        $action = fn() => 'test';
        $router = new Router();
        $router->get($uri, $action);

        $this->assertEquals($action, $router->resolve($uri, HttpMethod::GET->value));
    }

    public function testResolveMultipleBasicRoutesWithCallbackAction() {
        $routes = [
            '/test' => fn() => 'test',
            '/something' => fn() => 'something',
            'fizz' => fn() => 'fizz'
        ];

        $router = new Router();
        
        foreach ($routes as $uri => $action) {
            $router->get($uri, $action);
        }

        foreach($routes as $uri => $action) {
            $this->assertEquals($action, $router->resolve($uri, HttpMethod::GET->value));
        }
    }

    public function testResolveMultipleBasicRoutesWithCallbackActionForDifferentHttpMethods() {
        $routes = [
            [HttpMethod::GET, "/test", fn () => "get"],
            [HttpMethod::POST, "/test", fn () => "post"],
            [HttpMethod::PUT, "/test", fn () => "put"],
            [HttpMethod::PATCH, "/test", fn () => "patch"],
            [HttpMethod::DELETE, "/test", fn () => "delete"],

            [HttpMethod::GET, '/random-route', fn () => 'get'],
            [HttpMethod::POST, '/other-random-route', fn () => 'post'],
            [HttpMethod::PUT, "/something", fn () => "put"],
            [HttpMethod::PATCH, "/other-router", fn () => "patch"],
            [HttpMethod::DELETE, "/d", fn () => "delete"],
        ];

        $router = new Router();

        foreach($routes as [$method, $uri, $action]) {
            $router->{strtolower($method->value)}($uri, $action);
        }

        foreach($routes as [$method, $uri, $action]) { 
            $this->assertEquals($action, $router->resolve($uri, $method->value));
        }
    }
}