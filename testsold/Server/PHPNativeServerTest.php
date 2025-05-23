<?php

namespace LightWeight\Tests\Server;

use LightWeight\Http\HttpMethod;
use LightWeight\Http\Request;
use LightWeight\Http\Response;
use LightWeight\Server\PHPNativeServer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the PHPNativeServer class, focusing on HTTP/WWW redirection features
 */
class PHPNativeServerTest extends TestCase
{
    private PHPNativeServer $server;
    private array $originalConfig = [];    /**
     * Set up before each test
     */    protected function setUp(): void
    {
        // Save original config for restoration later
        $this->originalConfig = \LightWeight\Config\Config::$config;

        // Reset the config array for our tests
        \LightWeight\Config\Config::$config = ['server' => []];

        // Create the server instance
        $this->server = new PHPNativeServer();
    }

    /**
     * Clean up after each test
     */
    protected function tearDown(): void
    {
        // Restore original config
        \LightWeight\Config\Config::$config = $this->originalConfig;
    }

    #[DataProvider('httpsRedirectionDataProvider')]
    public function testHttpsRedirection(
        bool $forceHttps,
        string $scheme,
        string $host,
        int $port,
        bool $shouldRedirect,
        ?string $expectedUrl = null
    ): void {
        // Set config values for this test using the helper method
        $this->setConfig([
            'force_https' => $forceHttps,
            'force_www' => false
        ]);

        // $this->debugConfig("Before testHttpsRedirection - forceHttps: " . var_export($forceHttps, true));

        $request = $this->createMockRequest($scheme, $host, $port);
        $response = $this->server->checkRedirects($request);
        if (!$shouldRedirect) {
            $this->assertNull($response, 'Should not redirect');
        } else {
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(301, $response->getStatus());
            $this->assertEquals($expectedUrl, $response->headers('Location'));
        }
    }
    #[DataProvider('wwwRedirectionDataProvider')]
    public function testWwwRedirection(
        bool $forceWww,
        string $host,
        bool $shouldRedirect,
        ?string $expectedUrl = null
    ): void {
        // Set config values for this test
        $this->setConfig([
            'force_https' => false,
            'force_www' => $forceWww
        ]);

        $request = $this->createMockRequest('http', $host);
        $response = $this->server->checkRedirects($request);
        if (!$shouldRedirect) {
            $this->assertNull($response, 'Should not redirect');
        } else {
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(301, $response->getStatus());
            $this->assertEquals($expectedUrl, $response->headers('location'));
        }
    }
    #[DataProvider('combinedRedirectionDataProvider')]
    public function testCombinedHttpsAndWwwRedirection(
        bool $forceHttps,
        bool $forceWww,
        string $scheme,
        string $host,
        bool $shouldRedirect,
        ?string $expectedUrl = null
    ): void {
        // Set config values for this test
        $this->setConfig([
            'force_https' => $forceHttps,
            'force_www' => $forceWww
        ]);

        $request = $this->createMockRequest($scheme, $host);
        $response = $this->server->checkRedirects($request);

        if (!$shouldRedirect) {
            $this->assertNull($response, 'Should not redirect');
        } else {
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(301, $response->getStatus());
            $this->assertEquals($expectedUrl, $response->headers('Location'));
        }
    }

    /**
     * Test that API routes are treated differently in App class, but this implementation
     * should still redirect them if they're passed directly to checkRedirects
     */
    public function testApiRouteRedirection(): void
    {
        // Set config values with both redirects enabled
        $this->setConfig([
            'force_https' => true,
            'force_www' => true
        ]);

        // API routes should still be redirected if passed directly to checkRedirects
        $request = $this->createMockRequest('http', 'example.com');
        $request->setUri('/api/users');

        $response = $this->server->checkRedirects($request);
        // Should redirect since App is responsible for skipping API redirects
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(301, $response->getStatus());
        $this->assertEquals('https://www.example.com/api/users', $response->headers('Location'));
    }

    /**
     * Test redirection with paths and query parameters
     */
    public function testRedirectionWithPathAndQuery(): void
    {
        // Set config values
        $this->setConfig([
            'force_https' => true,
            'force_www' => false
        ]);

        $request = $this->createMockRequest('http', 'example.com');
        $request->setUri('/products/123');
        $query = ['sort' => 'price', 'dir' => 'asc'];
        $request->setQueryParameters($query);

        $response = $this->server->checkRedirects($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(301, $response->getStatus());
        $this->assertEquals('https://example.com/products/123?sort=price&dir=asc', $response->headers('Location'));
    }

    /**
     * Helper method to set server configuration
     */
    private function setConfig(array $config): void
    {
        \LightWeight\Config\Config::$config['server'] = $config;
    }

    /**
     * Debug method to verify config values
     */
    private function debugConfig(string $label = ""): void
    {
        echo "\n$label Config: " . print_r(\LightWeight\Config\Config::$config, true);
        echo "\nConfig value from function: " . var_export(config('server.force_https', 'DEFAULT'), true);
        echo "\n";
    }

    /**
     * Data provider for HTTPS redirection tests
     */    public static function httpsRedirectionDataProvider(): array
    {
        return [
            // [forceHttps, scheme, host, port, shouldRedirect, expectedUrl]
            'http when force https is disabled' => [false, 'http', 'example.com', 80, false],
            'https when force https is disabled' => [false, 'https', 'example.com', 443, false],
            'http when force https is enabled' => [true, 'http', 'example.com', 80, true, 'https://example.com/'],
            'https when force https is enabled' => [true, 'https', 'example.com', 443, false],
            'http with non-standard port when force https enabled' => [true, 'http', 'example.com', 8080, true, 'https://example.com:8080/'],
            'https with non-standard port when force https enabled' => [true, 'https', 'example.com', 8443, false],
        ];
    }
    /**
     * Data provider for WWW redirection tests
     */    public static function wwwRedirectionDataProvider(): array
    {
        return [
            // [forceWww, host, shouldRedirect, expectedUrl]
            'non-www when force www is disabled' => [false, 'example.com', false, null],
            'www when force www is disabled' => [false, 'www.example.com', false, null],
            'non-www when force www is enabled' => [true, 'example.com', true, 'http://www.example.com/'],
            'www when force www is enabled' => [true, 'www.example.com', false, null],
            'subdomain when force www is enabled' => [true, 'sub.example.com', true, 'http://www.sub.example.com/'],
        ];
    }
    /**
     * Data provider for combined HTTPS and WWW redirection tests
     */
    public static function combinedRedirectionDataProvider(): array
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
     * Creates a Request object with the given parameters
     */
    private function createMockRequest(string $scheme, string $host, ?int $port = null): Request
    {
        $request = new Request();
        $request->setScheme($scheme);
        $request->setHost($host);

        if ($port !== null) {
            $request->setPort($port);
        } else {
            $port = ($scheme === 'https') ? 443 : 80;
            $request->setPort($port);
        }

        $request->setMethod(HttpMethod::GET);
        $request->setUri('/');
        $request->setQueryParameters([]);

        return $request;
    }
}
