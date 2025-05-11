<?php

namespace LightWeight\Tests\Http;

use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\HttpMethod;
use LightWeight\Http\Request;
use LightWeight\Routing\Route;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testRequestReturnsDataObtainedFromServerCorrectly()
    {
        $uri = '/test/route';
        $queryParams = ['a' => 'Query Param Test A', 'b' => 'Query Param Test B', 'c' => 'Query Param Test C', 'foo' => 1];
        $postData = ['data' => 'test', 'randomData' => 2];

        $request = (new Request())
            ->setUri($uri)
            ->setMethod(HttpMethod::POST)
            ->setQueryParameters($queryParams)
            ->setPostData($postData);

        $this->assertEquals($uri, $request->uri());
        $this->assertEquals($queryParams, $request->query());
        $this->assertEquals($postData, $request->data());
        $this->assertEquals(HttpMethod::POST, $request->method());
    }
    public function testDataReturnsValueIfKeyIsGiven()
    {
        $data = ['data' => 10, 'randomValue' => 'hello', 'c' => 'test', 'foo' => 1];
        $request = (new Request())->setPostData($data);

        $this->assertEquals($data, $request->data());
        $this->assertEquals(10, $request->data('data'));
        $this->assertEquals('hello', $request->data('randomValue'));
        $this->assertEquals('test', $request->data('c'));
        $this->assertEquals(1, $request->data('foo'));
    }
    public function testQueryReturnsValueIfKeyIsGiven()
    {
        $data = ['data' => 10, 'randomValue' => 'hello', 'c' => 'test', 'foo' => 1];
        $request = (new Request())->setPostData($data);

        $this->assertEquals($data, $request->data());
        $this->assertEquals(10, $request->data('data'));
        $this->assertEquals('hello', $request->data('randomValue'));
        $this->assertEquals('test', $request->data('c'));
        $this->assertEquals(1, $request->data('foo'));
        $data = ['data' => 10, 'randomValue' => 'hello', 'c' => 'test', 'foo' => 1];
        $request = (new Request())->setQueryParameters($data);

        $this->assertEquals($data, $request->query());
        $this->assertEquals(10, $request->query('data'));
        $this->assertEquals('hello', $request->query('randomValue'));
        $this->assertEquals('test', $request->query('c'));
        $this->assertEquals(1, $request->query('foo'));
    }
    public function test_route_parameters_returns_value_if_key_is_given()
    {
        $route = new Route('/test/{param}/foo/{bar}', fn () => "test");
        $request = (new Request())
            ->setRoute($route)
            ->setUri('/test/1/foo/2');

        $this->assertEquals($request->routeParameters('param'), 1);
        $this->assertEquals($request->routeParameters('bar'), 2);
        $this->assertNull($request->routeParameters("doesn't exist"));
    }
}
