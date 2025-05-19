<?php

namespace LightWeight\Tests\Server;

use LightWeight\Http\HttpMethod;
use LightWeight\Http\Request;
use LightWeight\Http\Response;
use LightWeight\Server\PHPNativeServer;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the PHPNativeServer class, focusing on HTTP/WWW redirection features
 */
class PHPNativeServerTest extends TestCase
{
    private PHPNativeServer $server;
    private array $configValues = [];

    /**
     * Set up before each test
     */
    protected function setUp(): void
    {
        $this->server = $this->getMockBuilder(PHPNativeServer::class)
            ->onlyMethods(['getConfigValue'])
            ->getMock();
            
        // Configure the mock to use our test config values
        $this->server->method('getConfigValue')
            ->willReturnCallback(function($key, $default) {
                return $this->configValues[$key] ?? $default;
            });
    }
    
    /**
     * @dataProvider httpsRedirectionDataProvider
     */
    public function testHttpsRedirection(
        bool $forceHttps, 
        string $scheme, 
        string $host,
        int $port,
        bool $shouldRedirect, 
        string $expectedUrl = null
    ): void {
        // Set config values for this test
        $this->configValues = [
            'server.force_https' => $forceHttps,
            'server.force_www' => false
        ];
        
        $request = $this->createMockRequest($scheme, $host, $port);
        $response = $this->server->checkRedirects($request);
        
        if (!$shouldRedirect) {
            $this->assertNull($response, 'Should not redirect');
        } else {
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(301, $response->getStatus());
            $this->assertEquals($expectedUrl, $response->headers()['Location']);
        }
    }
    
    /**
     * @dataProvider wwwRedirectionDataProvider
     */
    public function testWwwRedirection(
        bool $forceWww, 
        string $host, 
        bool $shouldRedirect, 
        string $expectedUrl = null
    ): void {
        // Set config values for this test
        $this->configValues = [
            'server.force_https' => false,
            'server.force_www' => $forceWww
        ];
        
        $request = $this->createMockRequest('http', $host);
        $response = $this->server->checkRedirects($request);
        
        if (!$shouldRedirect) {
            $this->assertNull($response, 'Should not redirect');
        } else {
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(301, $response->getStatus());
            $this->assertEquals($expectedUrl, $response->headers()['Location']);
        }
    }
    
    /**
     * @dataProvider combinedRedirectionDataProvider
     */
    public function testCombinedHttpsAndWwwRedirection(
        bool $forceHttps,
        bool $forceWww,
        string $scheme,
        string $host,
        bool $shouldRedirect,
        string $expectedUrl = null
    ): void {
        // Set config values for this test
        $this->configValues = [
            'server.force_https' => $forceHttps,
            'server.force_www' => $forceWww
        ];
        
        $request = $this->createMockRequest($scheme, $host);
        $response = $this->server->checkRedirects($request);
        
        if (!$shouldRedirect) {
            $this->assertNull($response, 'Should not redirect');
        } else {
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(301, $response->getStatus());
            $this->assertEquals($expectedUrl, $response->headers()['Location']);
        }
    }
    
    /**
     * Test that API routes are treated differently in App class, but this implementation 
     * should still redirect them if they're passed directly to checkRedirects
     */
    public function testApiRouteRedirection(): void
    {
        // Set config values with both redirects enabled
        $this->configValues = [
            'server.force_https' => true,
            'server.force_www' => true
        ];
        
        // API routes should still be redirected if passed directly to checkRedirects
        $request = $this->createMockRequest('http', 'example.com');
        $request->method('path')->willReturn('/api/users');
        
        $response = $this->server->checkRedirects($request);
        
        // Should redirect since App is responsible for skipping API redirects
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(301, $response->getStatus());
        $this->assertEquals('https://www.example.com/api/users', $response->headers()['Location']);
    }
    
    /**
     * Test redirection with paths and query parameters
     */
    public function testRedirectionWithPathAndQuery(): void
    {
        // Set config values
        $this->configValues = [
            'server.force_https' => true,
            'server.force_www' => false
        ];
        
        $request = $this->createMockRequest('http', 'example.com');
        $request->method('path')->willReturn('/products/123');
        $request->method('query')->willReturn(['sort' => 'price', 'dir' => 'asc']);
        
        $response = $this->server->checkRedirects($request);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(301, $response->getStatus());
        $this->assertEquals('https://example.com/products/123?sort=price&dir=asc', $response->headers()['Location']);
    }
    
    /**
     * Data provider for HTTPS redirection tests
     */
    public function httpsRedirectionDataProvider(): array
    {
        return [
            // [forceHttps, scheme, host, port, shouldRedirect, expectedUrl]
            'http when force https is disabled' => [false, 'http', 'example.com', 80, false],
            'https when force https is disabled' => [false, 'https', 'example.com', 443, false],
            'http when force https is enabled' => [true, 'http', 'example.com', 80, true, 'https://example.com/'],
            'https when force https is enabled' => [true, 'https', 'example.com', 443, false],
            'http with non-standard port when force https enabled' => [true, 'http', 'example.com', 8080, true, 'https://example.com/'],
            'https with non-standard port when force https enabled' => [true, 'https', 'example.com', 8443, false],
        ];
    }
    
    /**
     * Data provider for WWW redirection tests
     */
    public function wwwRedirectionDataProvider(): array
    {
        return [
            // [forceWww, host, shouldRedirect, expectedUrl]
            'non-www when force www is disabled' => [false, 'example.com', false],
            'www when force www is disabled' => [false, 'www.example.com', false],
            'non-www when force www is enabled' => [true, 'example.com', true, 'http://www.example.com/'],
            'www when force www is enabled' => [true, 'www.example.com', false],
            'subdomain when force www is enabled' => [true, 'sub.example.com', true, 'http://www.sub.example.com/'],
        ];
    }
    
    /**
     * Data provider for combined HTTPS and WWW redirection tests
     */
    public function combinedRedirectionDataProvider(): array
    {
        return [
            // [forceHttps, forceWww, scheme, host, shouldRedirect, expectedUrl]
            'http non-www when both disabled' => [false, false, 'http', 'example.com', false],
            'https non-www when both disabled' => [false, false, 'https', 'example.com', false],
            'http www when both disabled' => [false, false, 'http', 'www.example.com', false],
            'https www when both disabled' => [false, false, 'https', 'www.example.com', false],
            
            'http non-www when both enabled' => [true, true, 'http', 'example.com', true, 'https://www.example.com/'],
            'https non-www when both enabled' => [true, true, 'https', 'example.com', true, 'https://www.example.com/'],
            'http www when both enabled' => [true, true, 'http', 'www.example.com', true, 'https://www.example.com/'],
            'https www when both enabled' => [true, true, 'https', 'www.example.com', false],
            
            'http non-www when https enabled, www disabled' => [true, false, 'http', 'example.com', true, 'https://example.com/'],
            'http www when https enabled, www disabled' => [true, false, 'http', 'www.example.com', true, 'https://www.example.com/'],
            
            'http non-www when https disabled, www enabled' => [false, true, 'http', 'example.com', true, 'http://www.example.com/'],
            'https non-www when https disabled, www enabled' => [false, true, 'https', 'example.com', true, 'https://www.example.com/'],
        ];
    }
    
    /**
     * Creates a mock Request object with the given parameters
     */
    private function createMockRequest(string $scheme, string $host, int $port = null): Request
    {
        $request = $this->createMock(Request::class);
        
        $request->method('scheme')->willReturn($scheme);
        $request->method('host')->willReturn($host);
        $request->method('path')->willReturn('/');
        $request->method('query')->willReturn([]);
        
        if ($port !== null) {
            $request->method('port')->willReturn($port);
        } else {
            $port = ($scheme === 'https') ? 443 : 80;
            $request->method('port')->willReturn($port);
        }
        
        return $request;
    }
}
