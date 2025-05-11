<?php

namespace LightWeight\Tests\App;

use LightWeight\App;
use LightWeight\Config\Config;
use LightWeight\Container\Container;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\Exceptions\DatabaseException;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Contracts\ResponseContract;
use LightWeight\Http\HttpMethod;
use LightWeight\Http\HttpNotFoundException;
use LightWeight\Http\Request;
use LightWeight\Http\Response;
use LightWeight\Routing\Router;
use LightWeight\Server\Contracts\ServerContract;
use LightWeight\Session\Contracts\SessionStorageContract;
use LightWeight\Session\Session;
use LightWeight\Validation\Exceptions\ValidationException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
    private App $app;
    private MockObject $routerMock;
    private MockObject $serverMock;
    private Request $requestMock;
    private MockObject $sessionMock;
    private MockObject $sessionStorageMock;
    private MockObject $databaseMock;

    /**
     * Helper method to create a Request object for testing
     *
     * @param string $uri The URI
     * @param string $method The HTTP method
     * @param array $queryParams Query parameters
     * @param array $postData POST data
     * @param array $headers HTTP headers
     * @return Request
     */
    private function createRequest(string $uri, string $method = 'GET', array $queryParams = [], array $postData = [], array $headers = []): Request
    {
        $request = new Request();
        $request->setUri($uri)
                ->setMethod(HttpMethod::from($method))
                ->setQueryParameters($queryParams)
                ->setPostData($postData);
                
        // Asegurarnos que los headers se configuran correctamente
        foreach ($headers as $key => $value) {
            $request->setHeader($key, $value);
        }
        
        return $request;
    }

    protected function setUp(): void
    {
        // Save current root path if App::$root is already set
        $oldRoot = App::$root ?? null;
        
        // Set up test root
        App::$root = __DIR__ . '/../..';
        
        // Set up mocks
        $this->routerMock = $this->createMock(Router::class);
        $this->serverMock = $this->createMock(ServerContract::class);
        $this->requestMock = $this->createRequest('/test');
        $this->sessionStorageMock = $this->createMock(SessionStorageContract::class);
        
        // Create session mock with proper constructor
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->setConstructorArgs([$this->sessionStorageMock])
            ->getMock();
            
        $this->databaseMock = $this->createMock(DatabaseDriverContract::class);
        
        // Configure the container to use our mocks
        Container::deleteInstance();
        $container = Container::getInstance();
        
        // Register our mocks with the container
        $container->set(Router::class, $this->routerMock);
        $container->set(ServerContract::class, $this->serverMock);
        $container->set(RequestContract::class, $this->requestMock);
        $container->set(Session::class, $this->sessionMock);
        $container->set(DatabaseDriverContract::class, $this->databaseMock);
        
        // Configure app with our mocks
        $this->app = new App();
        $this->app->router = $this->routerMock;
        $this->app->server = $this->serverMock;
        $this->app->request = $this->requestMock;
        $this->app->session = $this->sessionMock;
        $this->app->database = $this->databaseMock;
        
        // Setup basic method responses
        $this->serverMock->method('getRequest')->willReturn($this->requestMock);

        // Setup default config values for testing
        $this->setupTestConfig();
        
        // Restore root path if needed
        if ($oldRoot) {
            App::$root = $oldRoot;
        }
    }
    
    protected function setupTestConfig(): void
    {
        // Create a mock configuration for testing
        $_ENV['APP_DEBUG'] = 'false';
        
        // Load configuration into the Config class
        Config::load(__DIR__ . '/../Config/test-config');
        
        // We need to add the cors configuration directly to $_ENV
        // since we can't use Config::set
        $_ENV['CORS_ALLOWED_ORIGINS'] = 'https://allowed-domain.com';
        $_ENV['CORS_ALLOWED_METHODS'] = 'GET,POST,PUT,DELETE,OPTIONS';
        $_ENV['CORS_ALLOWED_HEADERS'] = 'Content-Type,Authorization';
        $_ENV['CORS_EXPOSED_HEADERS'] = 'X-Custom-Header';
        $_ENV['CORS_ALLOW_CREDENTIALS'] = 'true';
        $_ENV['CORS_MAX_AGE'] = '3600';
    }

    protected function tearDown(): void
    {
        Container::deleteInstance();
        Config::$config = [];
        unset($this->app);
    }

    public function testPrepareNextRequest(): void
    {
        // Create a new request with GET method
        $this->requestMock = $this->createRequest('/test/path', 'GET');
        
        // Update app with the new request
        $this->app->request = $this->requestMock;
        
        // Expect session to store the previous URI
        $this->sessionMock->expects($this->once())
            ->method('set')
            ->with('_previous', '/test/path');
        
        $this->app->prepareNextRequest();
    }
    
    public function testPrepareNextRequestSkipsIfNotGetMethod(): void
    {
        // Create a request with POST method
        $this->requestMock = $this->createRequest('/test', 'POST');
        
        // Update app with the new request
        $this->app->request = $this->requestMock;
        
        // Expect session set is never called
        $this->sessionMock->expects($this->never())->method('set');
        
        $this->app->prepareNextRequest();
    }
    
    public function testTerminate(): void
    {
        // Create a response mock
        $response = Response::text('Test Response');
        
        // Use a GET request for prepareNextRequest
        $this->requestMock = $this->createRequest('/test', 'GET');
        $this->app->request = $this->requestMock;
        
        // Expect session set to be called once (from prepareNextRequest)
        $this->sessionMock->expects($this->once())->method('set');
        
        // Expect sendResponse to be called
        $this->serverMock->expects($this->once())
            ->method('sendResponse')
            ->with($this->callback(function($resp) {
                return $resp instanceof Response;
            }));
        
        // Expect database connection to be closed
        $this->databaseMock->expects($this->once())
            ->method('close');
        
        $this->app->terminate($response);
    }
    
    public function testRun(): void
    {
        // Create a response mock
        $response = Response::text('Success');
        
        // Set up a request and update the app
        $this->requestMock = $this->createRequest('/test');
        $this->app->request = $this->requestMock;
        
        // Router should resolve the request to a response
        $this->routerMock->expects($this->once())
            ->method('resolve')
            ->with($this->requestMock)
            ->willReturn($response);
        
        // Expect sendResponse to be called
        $this->serverMock->expects($this->once())
            ->method('sendResponse')
            ->with($this->callback(function($resp) {
                return $resp instanceof Response;
            }));
        
        $this->app->run();
    }
    
    public function testHandleHttpNotFoundException(): void
    {
        // Set up a request and update the app
        $this->requestMock = $this->createRequest('/nonexistent-path');
        $this->app->request = $this->requestMock;
        
        // Router throws HttpNotFoundException
        $this->routerMock->expects($this->once())
            ->method('resolve')
            ->with($this->requestMock)
            ->willThrowException(new HttpNotFoundException());
        
        // Expect sendResponse to be called with a 404 response
        $this->serverMock->expects($this->once())
            ->method('sendResponse')
            ->with($this->callback(function(ResponseContract $response) {
                return $response->getStatus() === 404 &&
                       $response->getContent() === 'Not Found';
            }));
        
        $this->app->run();
    }
    
    public function testHandleValidationExceptionForApi(): void
    {
        // Setup request with API URI
        $this->requestMock = $this->createRequest('/api/test');
        $this->app->request = $this->requestMock;
        
        // Create validation exception with errors
        $validationException = new ValidationException(['name' => ['Name is required']]);
        
        // Router throws ValidationException
        $this->routerMock->expects($this->once())
            ->method('resolve')
            ->with($this->requestMock)
            ->willThrowException($validationException);
        
        // Expect sendResponse to be called with a 422 JSON response
        $this->serverMock->expects($this->once())
            ->method('sendResponse')
            ->with($this->callback(function(ResponseContract $response) {
                $content = json_decode($response->getContent(), true);
                return $response->getStatus() === 422 &&
                       $content['message'] === 'Validation Errors' &&
                       isset($content['errors']['name']);
            }));
        
        $this->app->run();
    }
    
    public function testHandleDatabaseException(): void
    {
        // Set APP_DEBUG to true to include trace info
        $_ENV['APP_DEBUG'] = 'true';
        
        // Set up a request and update the app
        $this->requestMock = $this->createRequest('/test-db-error');
        $this->app->request = $this->requestMock;
        
        // Create database exception
        $exception = new DatabaseException('Connection failed');
        
        // Router throws DatabaseException
        $this->routerMock->expects($this->once())
            ->method('resolve')
            ->with($this->requestMock)
            ->willThrowException($exception);
        
        // Expect sendResponse to be called with a 500 JSON response
        $this->serverMock->expects($this->once())
            ->method('sendResponse')
            ->with($this->callback(function(ResponseContract $response) {
                $content = json_decode($response->getContent(), true);
                return $response->getStatus() === 500 &&
                       $content['message'] === 'Connection failed' &&
                       isset($content['error']);
            }));
        
        $this->app->run();
        
        // Reset environment
        $_ENV['APP_DEBUG'] = 'false';
    }
    
    public function testSetCors(): void
    {
        // Create a response
        $response = Response::text('Test Response');
        
        // Setup request with Origin header
        $this->requestMock = $this->createRequest(
            '/test',
            'GET',
            [],
            [],
            ['Origin' => 'https://allowed-domain.com']
        );
        $this->app->request = $this->requestMock;
        
        // Apply CORS headers
        $this->app->setCors($response);
        
        // Verify headers were set correctly
        $this->assertEquals('https://allowed-domain.com', $response->headers('access-control-allow-origin'));
        $this->assertStringContainsString('GET', $response->headers('Access-Control-Allow-Methods'));
        $this->assertStringContainsString('POST', $response->headers('Access-Control-Allow-Methods'));
        $this->assertStringContainsString('Content-Type', $response->headers('Access-Control-Allow-Headers'));
        $this->assertEquals('true', $response->headers('Access-Control-Allow-Credentials'));
    }
    
    public function testSetCorsWithWildcardOrigin(): void
    {
        // Set wildcard origin in environment
        $_ENV['CORS_ALLOWED_ORIGINS'] = '*';
        
        // Setup request with Origin header
        $this->requestMock = $this->createRequest(
            '/test',
            'GET',
            [],
            [],
            ['Origin' => 'https://any-domain.com']
        );
        $this->app->request = $this->requestMock;

        Config::$config['cors']['allowed_origins'] = ['*'];
        
        // Create a response
        $response = Response::text('Test Response');
        
        // Apply CORS headers
        $this->app->setCors($response);
        
        // Verify headers were set correctly
        $this->assertEquals('*', $response->headers('access-control-allow-origin'));
    }
    
    public function testAbort(): void
    {
        // Create a response
        $response = Response::text('Aborted')->setStatus(400);
        
        // Expect sendResponse to be called
        $this->serverMock->expects($this->once())
            ->method('sendResponse')
            ->with($response);
        
        // Expect database connection to be closed
        $this->databaseMock->expects($this->once())
            ->method('close');
        
        $this->app->abort($response);
    }
}
