<?php

namespace LightWeight\Http;

class ControllerBase
{
    /**
     * HTTP middlewares.
     *
     * @var \LightWeight\Http\Contracts\MiddlewareContract[]
     */
    protected array $middlewares = [];

    /**
     * The current HTTP request.
     *
     * @var \LightWeight\Http\Request
     */
    protected $request;

    /**
     * The view renderer.
     *
     * @var ?string
     */
    protected ?string $view;

    /**
     * The logger instance.
     *
     * @var \LightWeight\Log\Contracts\LoggerContract
     */
    protected $logger;

    /**
     * Constructor to initialize controller with common services.
     */
    public function __construct()
    {
        $this->request = request();
        $this->view = app()->has('view') ? app('view') : null;
        $this->logger = logger();
    }

    /**
     * Get all HTTP middlewares for this route.
     *
     * @return \LightWeight\Http\Contracts\MiddlewareContract[]
     */
    public function middlewares(): array
    {
        return $this->middlewares;
    }

    public function setMiddlewares(array $middlewares): self
    {
        $this->middlewares = array_map(fn ($middleware) => new $middleware(), $middlewares);
        return $this;
    }

    /**
     * Get the current request instance.
     *
     * @return \LightWeight\Http\Request
     */
    protected function request()
    {
        return $this->request;
    }

    /**
     * Get the response instance.
     *
     * @return \LightWeight\Http\Response
     */
    protected function response()
    {
        return response();
    }

    /**
     * Render a view with data.
     *
     * @param string $view View name
     * @param array $data Data to pass to the view
     * @param int $status HTTP status code
     * @return \LightWeight\Http\Response
     */
    protected function view(string $view, array $data = [], int $status = 200)
    {
        if (!$this->view) {
            throw new \RuntimeException('View renderer not available');
        }

        return view($view, $data)->setStatus($status);
    }

    /**
     * Return a JSON response.
     *
     * @param mixed $data Data to convert to JSON
     * @param int $status HTTP status code
     * @return \LightWeight\Http\Response
     */
    protected function json($data, int $status = 200)
    {
        return json($data, $status);
    }

    /**
     * Return a redirect response.
     *
     * @param string $url URL to redirect to
     * @param int $status HTTP status code
     * @return \LightWeight\Http\Response
     */
    protected function redirect(string $url, int $status = 302)
    {
        return redirect($url)->setStatus($status);
    }

    /**
     * Return a "not found" response.
     *
     * @param string $message The not found message
     * @param int $status HTTP status code (default: 404)
     * @return \LightWeight\Http\Response
     */
    protected function notFound(string $message = 'Not Found', int $status = 404)
    {
        return $this->json(['error' => $message], $status);
    }

    /**
     * Return a validation error response.
     *
     * @param array $errors Validation errors
     * @param int $status HTTP status code (default: 422)
     * @return \LightWeight\Http\Response
     */
    protected function validationError(array $errors, int $status = 422)
    {
        return $this->json(['errors' => $errors], $status);
    }

    /**
     * Return an error response.
     *
     * @param string $message Error message
     * @param int $status HTTP status code (default: 500)
     * @return \LightWeight\Http\Response
     */
    protected function error(string $message, int $status = 500)
    {
        return $this->json(['error' => $message], $status);
    }
    
    /**
     * Apply middleware to the controller actions.
     *
     * @param string|array $middleware The middleware or array of middleware
     * @param array $only Apply only to specific methods
     * @param array $except Apply to all except specific methods
     * @return self
     */
    public function middleware($middleware, array $only = [], array $except = []): self
    {
        $middlewares = is_array($middleware) ? $middleware : [$middleware];
        
        foreach ($middlewares as $mw) {
            $this->middlewares[] = [
                'middleware' => $mw,
                'only' => $only,
                'except' => $except
            ];
        }
        
        return $this;
    }
    
    /**
     * Validate the request data against the provided rules.
     *
     * @param array $rules Validation rules
     * @param array $messages Custom error messages (if supported)
     * @return array The validated data
     * @throws \Exception If validation fails
     */
    protected function validate(array $rules, array $messages = []): array
    {
        return $this->request->validate($rules, $messages);
    }
    
    /**
     * Validate the request data against the provided rules, but don't throw exceptions.
     *
     * @param array $rules Validation rules
     * @param array $messages Custom error messages (if supported)
     * @return array|false The validated data or false if validation fails
     */
    protected function validateSilent(array $rules, array $messages = []): array|false
    {
        try {
            return $this->request->validate($rules, $messages);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Log a message with specified level.
     *
     * @param string $message The message to log
     * @param array $context The context data
     * @param string $level The log level (default: info)
     * @return void
     */
    protected function log(string $message, array $context = [], string $level = 'info'): void
    {
        if (!$this->logger) {
            return;
        }
        
        $this->logger->log($level, $message, $context);
    }

    /**
     * Return a successful response with data.
     *
     * @param mixed $data The data to return
     * @param int $status HTTP status code (default: 200)
     * @return \LightWeight\Http\Response
     */
    protected function success($data = null, int $status = 200)
    {
        if ($data === null) {
            $data = ['status' => 'success'];
        } elseif (is_string($data)) {
            $data = ['message' => $data];
        }
        
        return $this->json($data, $status);
    }
    
    /**
     * Return a created response with optional data.
     *
     * @param mixed $data The data to return (optional)
     * @return \LightWeight\Http\Response
     */
    protected function created($data = null)
    {
        return $this->success($data, 201);
    }
    
    /**
     * Return a no content response.
     *
     * @return \LightWeight\Http\Response
     */
    protected function noContent()
    {
        return $this->response()->setStatus(204);
    }

    /**
     * Return a forbidden response.
     * 
     * @param string $message The error message
     * @return \LightWeight\Http\Response
     */
    protected function forbidden(string $message = 'Forbidden')
    {
        return $this->json(['error' => $message], 403);
    }
    
    /**
     * Return an unauthorized response.
     * 
     * @param string $message The error message
     * @return \LightWeight\Http\Response
     */
    protected function unauthorized(string $message = 'Unauthorized')
    {
        return $this->json(['error' => $message], 401);
    }

    /**
     * Get the view renderer.
     *
     * @return mixed
     */
    protected function viewRenderer()
    {
        return $this->view;
    }
    
    /**
     * Get data from the request.
     *
     * @param string|null $key Specific data key to retrieve
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    protected function input(?string $key = null, $default = null)
    {
        $data = $this->request->data($key);
        
        if ($key !== null && $data === null) {
            return $default;
        }
        
        return $data;
    }
    
    /**
     * Get query string parameter from the request.
     *
     * @param string|null $key Specific parameter to retrieve
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    protected function query(?string $key = null, $default = null)
    {
        $data = $this->request->query($key);
        
        if ($key !== null && $data === null) {
            return $default;
        }
        
        return $data;
    }

    /**
     * Check if the current user is authenticated.
     *
     * @return bool
     */
    protected function isAuthenticated(): bool
    {
        return app(\LightWeight\App::class)
            ->has(\LightWeight\Auth\Contracts\Authenticators\AuthenticatorContract::class) 
            && app(\LightWeight\Auth\Contracts\Authenticators\AuthenticatorContract::class)->resolve() !== null;
    }
    
    /**
     * Get the currently authenticated user.
     *
     * @return mixed
     */
    protected function user()
    {
        return app(\LightWeight\Auth\Contracts\Authenticators\AuthenticatorContract::class)->resolve();
    }
    
    /**
     * Authorize a specific action based on a condition.
     *
     * @param bool $condition The authorization condition
     * @param string $message The error message if unauthorized
     * @return bool|Response Returns true if authorized, otherwise returns a response
     */
    protected function authorize(bool $condition, string $message = 'This action is unauthorized')
    {
        if ($condition) {
            return true;
        }
        
        return $this->forbidden($message);
    }

    /**
     * Get the base URL for the application.
     *
     * @return string
     */
    protected function baseUrl(): string
    {
        return config('app.url', '');
    }
    
    /**
     * Get all route parameters.
     *
     * @return array
     */
    protected function routeParameters(): array
    {
        return $this->request->routeParameters();
    }
    
    /**
     * Get a specific route parameter.
     *
     * @param string $key The parameter name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    protected function routeParameter(string $key, $default = null)
    {
        $param = $this->request->routeParameters($key);
        return $param !== null ? $param : $default;
    }
}
