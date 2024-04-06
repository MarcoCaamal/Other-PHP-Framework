<?php
namespace Junk\Tests;
use Junk\Router;

use Junk\Request;
use Junk\HttpMethod;
use Junk\ServerContract;
use Junk\Tests\MockServer;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase {
    private function createMockRequest(string $uri, HttpMethod $httpMethod): Request {
        $mockServer = $this->createConfiguredMock(
            ServerContract::class,
            [
                'requestUri' => $uri,
                'requestMethod' => $httpMethod
            ]
        );

        return new Request($mockServer);
    }

    public function testResolveBasicRouteWithCallback() {
        $uri = '/test';
        $action = fn() => 'test';
        $router = new Router();
        $router->get($uri, $action);
        $route = $router->resolve($this->createMockRequest($uri, HttpMethod::GET)); 

        $this->assertEquals($action, $route->action());
        $this->assertEquals($uri, $route->uri());
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
            $route = $router->resolve($this->createMockRequest($uri, HttpMethod::GET));
            $this->assertEquals($action, $route->action());
            $this->assertEquals($uri, $route->uri());
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
            $route = $router->resolve($this->createMockRequest($uri, $method));
            $this->assertEquals($route->action(), $action);
            $this->assertEquals($route->uri(), $uri);
        }
    }
}