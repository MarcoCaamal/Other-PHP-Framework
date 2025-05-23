<?php

namespace LightWeight;

use Dotenv\Dotenv;
use Exception;
use LightWeight\Config\Config;
use LightWeight\Container\Container;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\Exceptions\DatabaseException;
use LightWeight\Events\Contracts\EventDispatcherContract;
use LightWeight\Exceptions\Contracts\ExceptionHandlerContract;
use LightWeight\Http\HttpMethod;
use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Contracts\ResponseContract;
use LightWeight\Http\Response;
use LightWeight\Log\Contracts\LoggerContract;
use LightWeight\Routing\Router;
use LightWeight\Server\Contracts\ServerContract;
use LightWeight\Session\Session;
use LightWeight\View\Contracts\ViewContract;
use Throwable;

/**
 * Main application class for the LightWeight framework.
 */
class Application
{
    //-------------------------------------------------------------------------
    // PROPIEDADES PÚBLICAS
    //-------------------------------------------------------------------------
    
    /**
     * Application root directory
     *
     * @var string
     */
    public static string $root;
    
    /**
     * DI Container instance
     *
     * @var Container
     */
    private Container $container;
    /**
     * Configuration instance
     *
     * @var Config
     */
    private Config $config;
    /**
     * Router instance for handling routes
     *
     * @var Router
     */
    private Router $router;
    
    /**
     * Current request instance
     *
     * @var RequestContract
     */
    private RequestContract $request;
    
    /**
     * Server handler instance
     *
     * @var ServerContract
     */
    private ServerContract $server;
    
    /**
     * View engine instance
     *
     * @var ViewContract
     */
    private ViewContract $view;
    
    /**
     * Session handler instance
     *
     * @var Session
     */
    private Session $session;
    
    /**
     * Database driver instance
     *
     * @var DatabaseDriverContract
     */
    private DatabaseDriverContract $database;
    
    /**
     * Exception handler instance
     *
     * @var ExceptionHandlerContract
     */
    private ExceptionHandlerContract $exceptionHandler;
    
    /**
     * Event dispatcher instance
     *
     * @var EventDispatcherContract
     */
    private EventDispatcherContract $events;

    /**
     * Logger instance
     *
     * @var LoggerContract
     */
    private LoggerContract $logger;

    //-------------------------------------------------------------------------
    // MÉTODOS PÚBLICOS
    //-------------------------------------------------------------------------

    public function __construct(
        Container $container,
    ) 
    {
        $this->container = $container;
    }

    /**
     * Bootstrap the application
     *
     * @param string $root The root directory of the application
     * @return Application
     */    
    public static function bootstrap(string $root): Application
    {
        self::$root = $root;
        
        try {
            // Crear el contenedor
            $container = new Container();
            $container->addDefinitions([
                'app.root' => \DI\value($root),
            ]);
            // Cargar definiciones básicas
            $container->addDefinitions(self::$root . '/config/container.php');
            
            // Recolectar definiciones de todos los providers ANTES de construir
            self::collectProviderDefinitions($container);
            
            // Activar caché en producción y construir el contenedor
            if(env('APP_ENV', 'production') === 'production') {
                $container->enableCache(self::$root . '/storage/cache/container');
            }
            $container->build();
            
            // Crear la aplicación con el contenedor ya construido
            $app = new Application($container);
            $app->container = $container; // Asegurar que el contenedor esté asignado a la aplicación
            $app
                ->loadConfig()
                ->runServiceProviders('boot')  // Ahora solo ejecuta registerServices()
                ->setUpLogging()
                ->setUpDatabaseConnection()
                ->setExceptionHandler()
                ->setUpEventSystem()
                ->runServiceProviders('runtime')
                ->setHttpHandlers();

            // Dispatch bootstrap completed event
            if (isset($app->events)) {
                $app->events->dispatch(new \LightWeight\Events\System\ApplicationBootstrapped());
            }
            
            return $app;
        } catch (Throwable $e) {
            // Use the bootstrap exception handler to handle any errors during startup
            $bootstrapHandler = new \LightWeight\Exceptions\BootstrapExceptionHandler();
            $bootstrapHandler->handleException($e);
            
            // This line won't be reached because handleException calls exit(1)
            exit(1);
        }
    }
      /**
     * Recolecta definiciones de todos los providers antes de compilar el contenedor
     * 
     * @param Container $container
     * @return void
     */
    private static function collectProviderDefinitions(Container $container): void
    {
        // Cargar configuración para obtener lista de providers
        $providerClasses = [];
        
        // Cargar directamente desde el archivo para evitar dependencia circular
        if (file_exists(self::$root . '/config/providers.php')) {
            $providers = require self::$root . '/config/providers.php';
            
            // Recolectar providers de boot y runtime (excluyendo CLI)
            $providerClasses = array_merge(
                $providers['boot'] ?? [],
                $providers['runtime'] ?? []
            );
        }
        
        // Recolectar definiciones de cada provider
        foreach ($providerClasses as $providerClass) {
            if (class_exists($providerClass)) {
                $provider = new $providerClass();
                if (method_exists($provider, 'getDefinitions')) {
                    $definitions = $provider->getDefinitions();
                    if (is_array($definitions) && !empty($definitions)) {
                        $container->addDefinitions($definitions);
                    }
                }
            }
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
            $this->checkForRedirects();
            // No routes defined and this is the homepage
            if ($this->router->isEmpty() && $this->request->uri() === '/') {
                $this->terminate($this->showWelcomePage());
                return;
            }
            
            $this->terminate($this->router->resolve($this->request));
        } catch (Throwable $e) {
            // Report exception if needed
            $this->exceptionHandler->report($e);

            // Render appropriate response based on exception type
            $response = $this->exceptionHandler->render($this->request, $e);
            
            // Send the response
            $this->abort($response);
        }
    }
    
    /**
     * Set up HTTP handlers and related components
     *
     * @return self
     */
    public function setHttpHandlers(): self
    {
        $this->router = app(Router::class);
        $this->server = app(ServerContract::class);
        $this->request = app(RequestContract::class);
        $this->session = app(Session::class);
        
        // No need to create a global response instance
        // Each controller/route will create its own response
        
        return $this;
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
     * Set up exception handler
     *
     * @return self
     */
    public function setExceptionHandler(): self
    {
        $handlerClass = config('app.exception_handler', \App\Exceptions\Handler::class);
        $this->exceptionHandler = singleton(ExceptionHandlerContract::class, $handlerClass);
        $this->exceptionHandler->register();
        
        return $this;
    }
    
    /**
     * Set up the event system
     *
     * @return self
     */
    public function setUpEventSystem(): self
    {
        $this->events = app(EventDispatcherContract::class);
        
        return $this;
    }
    
    /**
     * Set up the logging system
     *
     * @return self
     */
    public function setUpLogging(): self
    {
        // Set the logger instance
        $this->logger = app(LoggerContract::class);
        
        return $this;
    }
    
    /**
     * Apply CORS headers to the response
     * 
     * @param ResponseContract $response The response to apply CORS headers to
     * @return self
     */
    public function setCors(ResponseContract &$response): self
    {
        $allowedOrigins = config('cors.allowed_origins', []);
        $this->setAllowOriginHeader($response, $allowedOrigins);
        
        $response->setHeader(
            'Access-Control-Allow-Methods', 
            implode(',', config('cors.allowed_methods', [
                HttpMethod::GET->value, 
                HttpMethod::POST->value, 
                HttpMethod::PUT->value, 
                HttpMethod::DELETE->value, 
                HttpMethod::OPTIONS->value
            ]))
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
     * @param ResponseContract $response The response to send
     * @return void
     */
    public function terminate(ResponseContract $response): void
    {
        // Dispatch terminating event before termination
        if (isset($this->events)) {
            $this->events->dispatch(new \LightWeight\Events\System\ApplicationTerminating(['response' => $response]));
        }
        
        $this->prepareNextRequest();
        $this->setCors($response);
        $this->server->sendResponse($response);
        $this->closeDatabaseConnection();
    }
    
    /**
     * Abort the application with a response
     *
     * @param ResponseContract $response
     * @return void
     */
    public function abort(ResponseContract $response): void
    {
        $this->terminate($response);
    }
    
    /**
     * Bind a class or interface to a concrete implementation
     *
     * @param string $class
     * @param \Closure|string $definition
     * @return void
     */
    public function bind(string $class, \Closure|string $definition): void
    {
        $this->container->set($class, $definition);
    }
    
    /**
     * Create a new instance of a class
     *
     * @param string $class
     * @param array $parameters
     * @return mixed
     */
    public function make(string $class, array $parameters = []): mixed
    {
        return $this->container->make($class, $parameters);
    }
    
    /**
     * Create a singleton instance of a class
     *
     * @template T
     * @param class-string<T> $class
     * @param \Closure|class-string<T> $definition
     * @return T
     */
    public function singleton(string $class, \Closure|string $definition): mixed
    {
        return $this->container->get($class) ?? $this->container->set($class, $definition);
    }
    
    /**
     * Call a method on a class instance
     *
     * @param class-string $class
     * @param array $parameters
     * @return mixed
     */
    public function call(string $class, array $parameters = []): mixed
    {
        return $this->container->call($class, $parameters);
    }
    
    /**
     * Get a class instance from the container
     *
     * @template T
     * @param class-string<T> $class
     * @return T
     */
    public function get(string $class)
    {
        return $this->container->get($class);
    }
    
    /**
     * Check if a class is registered in the container
     *
     * @param string $class
     * @return bool
     */
    public function has(string $class): bool
    {
        return $this->container->has($class);
    }

    /**
     * Get the logger instance
     *
     * @return LoggerContract
     */
    public function log(): LoggerContract
    {
        return $this->logger;
    }
    
    //-------------------------------------------------------------------------
    // MÉTODOS PROTEGIDOS
    //-------------------------------------------------------------------------
    
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
     * Safely close database connection
     *
     * @return void
     */
    protected function closeDatabaseConnection(): void
    {
        if (isset($this->database)) {
            $this->database->close();
        }
    }    // El método setUpContainer ya no es necesario ya que la configuración
    // del contenedor ahora se realiza en el método bootstrap
    // antes de crear la instancia de Application
    /**
     * Load configuration files and environment variables
     *
     * @return self
     */
    protected function loadConfig(): self
    {
        $this->config = $this->container->get(Config::class);
        $this->config->loadFromDirectory(self::$root . '/config');
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
            $provider->registerServices($this->container);
        }
        return $this;
    }
    
    /**
     * Check if the request needs to be redirected for HTTPS or WWW enforcement
     *
     * @return void
     */
    protected function checkForRedirects(): void
    {
        // Skip redirect checks for API requests
        if ($this->isApiRequest()) {
            return;
        }
        
        // Check if redirect is needed for HTTPS or WWW
        if (method_exists($this->server, 'checkRedirects')) {
            $response = $this->server->checkRedirects($this->request);
            if ($response) {
                $this->terminate($response);
                exit;
            }
        }
    }
    
    /**
     * Set the Access-Control-Allow-Origin header on the response
     * 
     * @param ResponseContract $response The response to set headers on
     * @param array $allowedOrigins List of allowed origins
     * @return void
     */
    protected function setAllowOriginHeader(ResponseContract &$response, array $allowedOrigins): void
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
    
    //-------------------------------------------------------------------------
    // MÉTODOS PRIVADOS
    //-------------------------------------------------------------------------
    
    /**
     * Show welcome page when no routes are defined
     *
     * @return ResponseContract
     */
    private function showWelcomePage(): ResponseContract
    {
        try {
            // Try to show the welcome page
            return Response::view('welcome', [], false)
                ->setStatus(200);
        } catch (Throwable $e) {
            // Fallback to a simple text response if view can't be loaded
            return Response::text("LightWeight Framework installed successfully!")
                ->setStatus(200);
        }
    }
}
