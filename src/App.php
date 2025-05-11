<?php

namespace LightWeight;

use Dotenv\Dotenv;
use Exception;
use LightWeight\Config\Config;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\Exceptions\DatabaseException;
use LightWeight\Database\ORM\Model;
use LightWeight\Database\QueryBuilder\Drivers\MySQLQueryBuilder;
use LightWeight\Http\HttpMethod;
use LightWeight\Http\HttpNotFoundException;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Request;
use LightWeight\Http\Response;
use LightWeight\Routing\Router;
use LightWeight\Server\Contracts\ServerContract;
use LightWeight\Session\Contracts\SessionStorageContract;
use LightWeight\Session\Session;
use LightWeight\Validation\Exceptions\ValidationException;
use LightWeight\View\Contracts\ViewContract;
use Throwable;

/**
 * Main application class for the LightWeight framework.
 */
class App
{
    /**
     * Application root directory
     *
     * @var string
     */
    public static string $root;
    
    /**
     * Router instance for handling routes
     *
     * @var Router
     */
    public Router $router;
    
    /**
     * Current request instance
     *
     * @var RequestContract
     */
    public RequestContract $request;
    
    /**
     * Server handler instance
     *
     * @var ServerContract
     */
    public ServerContract $server;
    
    /**
     * View engine instance
     *
     * @var ViewContract
     */
    public ViewContract $view;
    
    /**
     * Session handler instance
     *
     * @var Session
     */
    public Session $session;
    
    /**
     * Database driver instance
     *
     * @var DatabaseDriverContract
     */
    public DatabaseDriverContract $database;
    
    /**
     * Prepare data for the next request
     * Stores current URI in session for use with back() helper
     *
     * @return void
     */
    public function prepareNextRequest(): void
    {
        if ($this->request->method() == HttpMethod::GET) {
            $this->session->set('_previous', $this->request->uri());
        }
    }
    /**
     * Terminate the application with a response
     *
     * @param Response $response The response to send
     * @return void
     */
    public function terminate(Response $response): void
    {
        $this->prepareNextRequest();
        $this->setCors($response);
        $this->server->sendResponse($response);
        $this->closeDatabaseConnection();
    }
    
    /**
     * Safely close database connection
     *
     * @return void
     */
    protected function closeDatabaseConnection(): void
    {
        if (isset($this->database)) {
            $this->database->close();
        }
    }

    /**
     * Bootstrap the application
     *
     * @param string $root The root directory of the application
     * @return App
     */
    public static function bootstrap(string $root): App
    {
        self::$root = $root;
        $app = singleton(App::class);
        return $app
            ->loadConfig()
            ->runServiceProviders('boot')
            ->setHttpHandlers()
            ->setUpDatabaseConnection()
            ->runServiceProviders('runtime');
    }
    
    /**
     * Load configuration files and environment variables
     *
     * @return self
     */
    protected function loadConfig(): self
    {
        Dotenv::createImmutable(self::$root)->load();
        Config::load(self::$root . "/config");
        return $this;
    }
    
    /**
     * Run service providers of a specific type
     *
     * @param string $type The type of service provider to run ('boot' or 'runtime')
     * @return self
     */
    protected function runServiceProviders(string $type): self
    {
        foreach (config("providers.$type", []) as $provider) {
            $provider = new $provider();
            $provider->registerServices(\LightWeight\Container\Container::getInstance());
        }
        return $this;
    }
    
    /**
     * Set up HTTP handlers and related components
     *
     * @return self
     */
    public function setHttpHandlers(): self
    {
        $this->router = singleton(Router::class);
        $this->server = app(ServerContract::class);
        $this->request = singleton(Request::class, fn () => $this->server->getRequest());
        $this->session = singleton(Session::class, fn (SessionStorageContract $sessionStorage) => new Session($sessionStorage));
        return $this;
    }
    /**
     * Apply CORS headers to the response
     * 
     * @param Response $response The response to apply CORS headers to
     * @return self
     */
    public function setCors(Response &$response): self
    {
        $allowedOrigins = config('cors.allowed_origins', []);
        $this->setAllowOriginHeader($response, $allowedOrigins);
        
        $response->setHeader(
            'Access-Control-Allow-Methods', 
            implode(',', config('cors.allowed_methods', ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']))
        );
        
        $response->setHeader(
            'Access-Control-Allow-Headers', 
            implode(',', config('cors.allowed_headers', ['Content-Type', 'Authorization']))
        );
        
        if (config('cors.exposed_headers', [])) {
            $response->setHeader(
                'Access-Control-Expose-Headers',
                implode(',', config('cors.exposed_headers', []))
            );
        }
        
        $response->setHeader(
            'Access-Control-Allow-Credentials', 
            config('cors.allow_credentials', 'false')
        );
        
        $maxAge = config('cors.max_age', 0);
        if ($maxAge > 0) {
            $response->setHeader('Access-Control-Max-Age', (string)$maxAge);
        }
        
        return $this;
    }
    
    /**
     * Set the Access-Control-Allow-Origin header on the response
     * 
     * @param Response $response The response to set headers on
     * @param array $allowedOrigins List of allowed origins
     * @return void
     */
    protected function setAllowOriginHeader(Response &$response, array $allowedOrigins): void
    {
        if (in_array('*', $allowedOrigins)) {
            $response->setHeader('Access-Control-Allow-Origin', '*');
        } else {
            $origin = $this->request->headers('Origin');
            if ($origin && in_array($origin, $allowedOrigins)) {
                $response->setHeader('Access-Control-Allow-Origin', $origin);
            }
        }
    }
    /**
     * Set up database connection and ORM configuration
     *
     * @return self
     * @throws DatabaseException If database connection fails
     */
    public function setUpDatabaseConnection(): self
    {
        try {
            $this->database = app(DatabaseDriverContract::class);
            $this->database->connect(
                config("database.connection"),
                config("database.host"),
                config("database.port"),
                config("database.database"),
                config("database.username"),
                config("database.password"),
            );
            
            return $this;
        } catch (Exception $e) {
            throw new DatabaseException("Failed to connect to database: {$e->getMessage()}", 0, $e);
        }
    }
    /**
     * Run the application
     *
     * @return void
     */
    public function run(): void
    {
        try {
            $this->terminate($this->router->resolve($this->request));
        } catch (HttpNotFoundException $e) {
            $this->handleHttpNotFound($e);
        } catch (ValidationException $e) {
            $this->handleValidationException($e);
        } catch (DatabaseException $e) {
            $this->handleDatabaseException($e);
        } catch (Throwable $e) {
            $this->handleGenericException($e);
        }
    }
    
    /**
     * Handle a not found exception
     *
     * @param HttpNotFoundException $e
     * @return void
     */
    protected function handleHttpNotFound(HttpNotFoundException $e): void
    {
        $this->abort(Response::text("Not Found")->setStatus(404));
    }
    
    /**
     * Handle a validation exception
     *
     * @param ValidationException $e
     * @return void
     */
    protected function handleValidationException(ValidationException $e): void
    {
        if ($this->isApiRequest()) {
            $response = Response::json([
                'errors' => $e->errors(),
                'message' => "Validation Errors",
            ])->setStatus(422);
            
            $this->abort($response);
        } else {
            $this->abort(back()->withErrors($e->errors(), 422));
        }
    }
    
    /**
     * Handle a database exception
     *
     * @param DatabaseException $e
     * @return void
     */
    protected function handleDatabaseException(DatabaseException $e): void
    {
        $response = $this->createExceptionResponse($e, 500, 'Database Error');
        $this->abort($response);
    }
    
    /**
     * Handle a generic exception
     *
     * @param Throwable $e
     * @return void
     */
    protected function handleGenericException(Throwable $e): void
    {
        $response = $this->createExceptionResponse($e, 500, 'Server Error');
        $this->abort($response);
    }
    
    /**
     * Create a standardized error response for an exception
     *
     * @param Throwable $e The exception
     * @param int $status HTTP status code
     * @param string $message User-friendly message
     * @return Response
     */
    protected function createExceptionResponse(Throwable $e, int $status, string $message): Response
    {
        $data = [
            'error' => $e::class,
            'message' => $e->getMessage()
        ];
        
        if (env('APP_DEBUG', false) === true) {
            $data['file'] = $e->getFile();
            $data['line'] = $e->getLine();
            $data['trace'] = $e->getTrace();
        }
        
        return json($data)->setStatus($status);
    }
    
    /**
     * Check if the current request is an API request
     *
     * @return bool
     */
    protected function isApiRequest(): bool
    {
        return str_starts_with($this->request->uri(), '/api');
    }
    
    /**
     * Abort the application with a response
     *
     * @param Response $response
     * @return void
     */
    public function abort(Response $response): void
    {
        $this->terminate($response);
    }
}
