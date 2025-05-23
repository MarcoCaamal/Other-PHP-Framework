<?php

namespace Tests\Http\Middleware;

use LightWeight\Http\HttpMethod;
use LightWeight\Http\Request;
use PHPUnit\Framework\TestCase;
use LightWeight\Http\Middleware\ApiExceptionHandlerMiddleware;
use LightWeight\Exceptions\Contracts\ExceptionHandlerContract;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Contracts\ResponseContract;
use LightWeight\Http\Response;

class ApiExceptionHandlerMiddlewareTest extends TestCase
{
    private $exceptionHandler;
    private $middleware;

    /**
     * Helper method to create a Request object for testing
     *
     * @param string $uri The URI
     * @param HttpMethod|string $method The HTTP method
     * @param array $queryParams Query parameters
     * @param array $postData POST data
     * @param array $headers HTTP headers
     * @return Request
     */
    private function createRequest(string $uri, HttpMethod|string $method = HttpMethod::GET, array $queryParams = [], array $postData = [], array $headers = []): Request
    {
        $request = new Request();
        $request->setUri($uri)
                ->setMethod($method instanceof HttpMethod ? $method : HttpMethod::from($method))
                ->setQueryParameters($queryParams)
                ->setPostData($postData);

        // Set headers if provided
        foreach ($headers as $key => $value) {
            $request->setHeader($key, $value);
        }

        return $request;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock exception handler
        $this->exceptionHandler = $this->createMock(ExceptionHandlerContract::class);

        // Create middleware instance
        $this->middleware = new ApiExceptionHandlerMiddleware($this->exceptionHandler);
    }

    public function testHandleSuccessfulRequest(): void
    {
        // Create a request
        $request = $this->createRequest('/api/test', HttpMethod::GET);

        // Create a mock response for the next middleware
        $expectedResponse = Response::json(['success' => true]);

        // Create a next middleware function that returns the expected response
        $next = function ($request) use ($expectedResponse) {
            return $expectedResponse;
        };

        // Call the middleware
        $actualResponse = $this->middleware->handle($request, $next);

        // Assert that the middleware returns the response from the next middleware
        $this->assertSame($expectedResponse, $actualResponse);
    }

    public function testHandleExceptionInRequest(): void
    {
        // Create a mock exception
        $exception = new \Exception('Test exception');
        $requestMock = $this->createRequest('/api/test', HttpMethod::GET);

        // Create a mock response for the rendered exception
        $expectedResponse = $this->createMock(ResponseContract::class);
        $expectedResponse->expects($this->once())
            ->method('setHeader')
            ->with('Content-Type', 'application/json')
            ->willReturnSelf();

        // Configure exception handler expectations
        $this->exceptionHandler->expects($this->once())
            ->method('report')
            ->with($exception);

        $this->exceptionHandler->expects($this->once())
            ->method('render')
            ->with($requestMock, $exception)
            ->willReturn($expectedResponse);

        // Create a next middleware function that throws an exception
        $next = function ($request) use ($exception) {
            throw $exception;
        };

        // Call the middleware
        $actualResponse = $this->middleware->handle($requestMock, $next);

        // Assert that the middleware returns the response from the exception handler
        $this->assertSame($expectedResponse, $actualResponse);
    }
}
