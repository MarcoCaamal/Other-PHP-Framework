<?php

namespace Tests\Exceptions;

use LightWeight\Exceptions\Contracts\ExceptionHandlerContract;
use LightWeight\Exceptions\ExceptionHandler;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Contracts\ResponseContract;
use LightWeight\Http\HttpMethod;
use LightWeight\Http\HttpNotFoundException;
use LightWeight\Http\Request;
use LightWeight\Http\Response;
use LightWeight\Routing\Route;
use LightWeight\Storage\File;
use LightWeight\Validation\Exceptions\ValidationException;
use LightWeight\Database\Exceptions\DatabaseException;
use PHPUnit\Framework\TestCase;

class MockHandler extends ExceptionHandler
{
    public function register(): void
    {
        // Register handlers for specific exception types
        $this->registerHandler(
            HttpNotFoundException::class,
            function(HttpNotFoundException $e, RequestContract $request) {
                return Response::text('Not Found')->setStatus(404);
            }
        );
        
        $this->registerHandler(
            ValidationException::class,
            function(ValidationException $e, RequestContract $request) {
                $isApi = str_starts_with($request->uri(), '/api');
                
                return Response::json([
                    'errors' => $e->errors(),
                    'message' => "Validation Errors",
                ])->setStatus(422);
            }
        );
        
        $this->registerHandler(
            DatabaseException::class,
            function(DatabaseException $e, RequestContract $request) {
                return Response::json([
                    'message' => $e->getMessage(),
                    'error' => 'Database Error'
                ])->setStatus(500);
            }
        );
        
        // Register a custom handler for generic exceptions
        $this->registerHandler(
            \Exception::class,
            function(\Exception $e, RequestContract $request) {
                return Response::json([
                    'custom_handler' => true,
                    'message' => $e->getMessage()
                ])->setStatus(500);
            }
        );
    }
}

class ExceptionHandlerTest extends TestCase
{
    private ExceptionHandlerContract $handler;
    private RequestContract $request;

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
        $this->handler = new MockHandler();
        $this->handler->register();
        
        // Create a concrete request implementation for testing
        $this->request = $this->createRequest('/test');
    }

    public function testRenderHttpNotFoundException(): void
    {
        $exception = new HttpNotFoundException('Page not found');
        $response = $this->handler->render($this->request, $exception);
        
        $this->assertEquals(404, $response->getStatus());
    }

    public function testRenderValidationException(): void
    {
        $errors = ['name' => ['Name is required']];
        $exception = new ValidationException($errors);
        
        // Test regular web request
        $response = $this->handler->render($this->request, $exception);
        $this->assertEquals(422, $response->getStatus());
        
        // Test API request
        $apiRequest = $this->createRequest('/api/test');
        
        $response = $this->handler->render($apiRequest, $exception);
        $this->assertEquals(422, $response->getStatus());
        
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Validation Errors', $content['message']);
        $this->assertEquals($errors, $content['errors']);
    }

    public function testRenderDatabaseException(): void
    {
        $exception = new DatabaseException('Database connection failed');
        $response = $this->handler->render($this->request, $exception);
        
        $this->assertEquals(500, $response->getStatus());
        
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Database connection failed', $content['message']);
    }

    public function testCustomExceptionHandler(): void
    {
        $exception = new \Exception('Test exception');
        $response = $this->handler->render($this->request, $exception);
        
        $this->assertEquals(500, $response->getStatus());
        
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['custom_handler']);
        $this->assertEquals('Test exception', $content['message']);
    }

    public function testShouldReport(): void
    {
        // HttpNotFoundException and ValidationException should not be reported
        $notFoundException = new HttpNotFoundException('Not found');
        $this->assertFalse($this->handler->shouldReport($notFoundException));
        
        $validationException = new ValidationException(['field' => ['error']]);
        $this->assertFalse($this->handler->shouldReport($validationException));
        
        // DatabaseException should be reported
        $dbException = new DatabaseException('Database error');
        $this->assertTrue($this->handler->shouldReport($dbException));
    }
}
