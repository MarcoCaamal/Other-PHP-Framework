<?php

namespace LightWeight\Tests\App;

use LightWeight\App;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Contracts\ResponseContract;
use LightWeight\Http\HttpMethod;
use LightWeight\Http\Request;
use LightWeight\Http\Response;
use LightWeight\Server\Contracts\ServerContract;
use PHPUnit\Framework\TestCase;

class RedirectTest extends TestCase
{
    private App $app;
    private $serverMock;
    private $requestMock;
    
    protected function setUp(): void
    {
        // Create mocks for the server and request
        $this->serverMock = $this->createMock(ServerContract::class);
        $this->requestMock = new Request();
        
        // Create and set up App instance
        $this->app = new App();
        
        // Inject the mocks into the app
        $this->setPrivateProperty($this->app, 'server', $this->serverMock);
        $this->setPrivateProperty($this->app, 'request', $this->requestMock);
    }
    
    /**
     * Test that API requests are not redirected
     */
    public function testNoRedirectForApiRequests(): void
    {
        // Set up request to be an API request
        $this->requestMock->setUri('/api/users');
        $this->requestMock->setMethod(HttpMethod::GET);
        
        // Server should not be asked to check redirects
        $this->serverMock->expects($this->never())
            ->method('checkRedirects');
        
        // Call the method (make it accessible)
        $this->invokePrivateMethod($this->app, 'checkForRedirects');
    }
    
    /**
     * Test that non-API requests are checked for redirection
     */
    public function testCheckRedirectsForNonApiRequests(): void
    {
        // Set up request to be a regular request
        $this->requestMock
            ->setUri('/users')
            ->setMethod(HttpMethod::GET);

        // Server should be asked to check redirects
        $this->serverMock->expects($this->once())
            ->method('checkRedirects')
            ->with($this->requestMock)
            ->willReturn(null);
        
        // Call the method (make it accessible)
        $this->invokePrivateMethod($this->app, 'checkForRedirects');
    }
    
    /**
     * Test redirection when server returns a response
     */
    public function testRedirectWhenServerReturnsResponse(): void
    {
        // Set up request to be a regular request
        $this->requestMock
            ->setUri('/users')
            ->setMethod(HttpMethod::GET);
        
        // Create a sample response for redirection
        $responseMock = $this->createMock(Response::class);
        
        // Server returns a response, indicating redirection is needed
        $this->serverMock->expects($this->once())
            ->method('checkRedirects')
            ->with($this->requestMock)
            ->willReturn($responseMock);
        
        // Expect terminate to be called with the response
        $app = $this->getMockBuilder(App::class)
            ->onlyMethods(['terminate'])
            ->setConstructorArgs([])
            ->getMock();
        
        $app->expects($this->once())
            ->method('terminate')
            ->with($responseMock);
        
        // Inject the mocks into the app
        $this->setPrivateProperty($app, 'server', $this->serverMock);
        $this->setPrivateProperty($app, 'request', $this->requestMock);
        
        // This test requires careful handling of the exit() call
        $this->expectOutputString('');
        
        try {
            // Call the method (make it accessible)
            $this->invokePrivateMethod($app, 'checkForRedirects');
        } catch (\Exception $e) {
            // We expect an exception since the method calls exit()
            $this->assertTrue(true);
        }
    }
    
    /**
     * Helper to invoke a private method on an object
     */
    private function invokePrivateMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        
        return $method->invokeArgs($object, $parameters);
    }
    
    /**
     * Helper to set a private property on an object
     */
    private function setPrivateProperty(object $object, string $propertyName, mixed $value): void
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
