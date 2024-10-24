<?php

namespace SMFramework\Tests\Http;

use PHPUnit\Framework\TestCase;

use SMFramework\Http\Response;

class ResponseTest extends TestCase
{
    public function testJsonResponseIsContructedCorrectly()
    {
        $content = ['tests' => 'test_value', 'foo' => 1, 'beer' => 'Modelo'];
        $response = Response::json($content);

        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals(json_encode($content), $response->getContent());
        $this->assertEquals(['content-type' => 'application/json'], $response->headers());
    }
    public function testTextResponseIsContructedCorrectly()
    {
        $content = 'This is a test text :D';
        $response = Response::text($content);

        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals($content, $response->getContent());
        $this->assertEquals(['content-type' => 'text/plain'], $response->headers());
    }
    public function testRedirectResponseIsContructedCorrectly()
    {
        $uri = '/route/redirect';
        $response = Response::redirect($uri);

        $this->assertEquals(302, $response->getStatus());
        $this->assertNull($response->getContent());
        $this->assertEquals(['location' => $uri], $response->headers());
    }
    public function testPrepareMethodRemovesContentHeadersIfThereIsNoContent()
    {
        $response = new Response();
        $response->setContentType('Test-Content');
        $response->setHeader('Content-Length', 10);
        $response->prepare();

        $this->assertEmpty($response->headers());
    }
    public function testPrepareMethodAddsContentLengthHeaderIfThereIsContent()
    {
        $content = 'test';
        $response = Response::text($content);
        $response->prepare();

        $this->assertEquals(strlen($content), $response->headers()['content-length']);
    }
}
