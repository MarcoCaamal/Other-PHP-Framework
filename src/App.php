<?php

namespace LightWeight;

use Dotenv\Dotenv;
use Exception;
use LightWeight\Config\Config;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\ORM\Model;
use LightWeight\Database\QueryBuilder\Drivers\MySQLQueryBuilder;
use LightWeight\Http\HttpMethod;
use LightWeight\Http\HttpNotFoundException;
use LightWeight\Http\Request;
use LightWeight\Http\Response;
use LightWeight\Routing\Router;
use LightWeight\Server\Contracts\ServerContract;
use LightWeight\Session\Contracts\SessionStorageContract;
use LightWeight\Session\Session;
use LightWeight\Validation\Exceptions\ValidationException;
use LightWeight\View\Contracts\ViewContract;

class App
{
    public static string $root;
    public Router $router;
    public Request $request;
    public ServerContract $server;
    public ViewContract $view;
    public Session $session;
    public DatabaseDriverContract $database;

    public function prepareNextRequest()
    {
        if ($this->request->method() == HttpMethod::GET) {
            $this->session->set('_previous', $this->request->uri());
        }
    }
    public function terminate(Response $response)
    {
        $this->prepareNextRequest();
        $this->setCors($response);
        $this->server->sendResponse($response);
        $this->database->close();
    }
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
    protected function loadConfig(): self
    {
        Dotenv::createImmutable(self::$root)->load();
        Config::load(self::$root . "/config");
        return $this;
    }
    protected function runServiceProviders(string $type): self
    {
        foreach (config("providers.$type", []) as $provider) {
            $provider = new $provider();
            $provider->registerServices(\LightWeight\Container\Container::getInstance());
        }
        return $this;
    }
    public function setHttpHandlers(): self
    {
        $this->router = singleton(Router::class);
        $this->server = app(ServerContract::class);
        $this->request = singleton(Request::class, fn () => $this->server->getRequest());
        $this->session = singleton(Session::class, fn (SessionStorageContract $sessionStorage) => new Session($sessionStorage));
        return $this;
    }
    public function setCors(&$response): self
    {
        $allowedOrigins = config('cors.allowed_origins', []);
        if(in_array('*', $allowedOrigins)) {
            $response->setHeader('Access-Control-Allow-Origin', '*');
        } else {
            $origin = $this->request->headers('Origin');
            if (in_array($origin, $allowedOrigins)) {
                $response->setHeader('Access-Control-Allow-Origin', $origin);
            }
        }
        $response
            ->setHeader('Access-Control-Allow-Methods', implode(',', config('cors.allowed_methods', ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'])));
        $response
            ->setHeader('Access-Control-Allow-Headers', implode(',', config('cors.allowed_headers', ['Content-Type', 'Authorization'])));
        $response
            ->setHeader('Access-Control-Allow-Credentials', config('cors.allow_credentials', 'false'));
        return $this;
    }
    public function setUpDatabaseConnection(): self
    {
        $this->database = app(DatabaseDriverContract::class);
        $this->database->connect(
            config("database.connection"),
            config("database.host"),
            config("database.port"),
            config("database.database"),
            config("database.username"),
            config("database.password"),
        );
        match(config('database.connection', 'mysql')) {
            'mysql' => Model::setBuilderClassString(MySQLQueryBuilder::class)
        };
        Model::setDatabaseDriver($this->database);
        return $this;
    }
    public function run()
    {
        try {
            $this->terminate($this->router->resolve($this->request));
        } catch (HttpNotFoundException $e) {
            $this->abort(Response::text("Not Found")->setStatus(404));
        } catch (ValidationException $e) {
            if(str_starts_with($this->request->uri(), '/api')) {
                $response = Response::json([
                    'errors' => $e->errors(),
                    'message' => "Validation Errors",
                ]);
                $this->abort($response->setStatus(422));

            } else {
                $this->abort(back()->withErrors($e->errors(), 422));
            }
        } catch (Exception $e) {
            $response = json([
                'error' => $e::class,
                'message' => $e->getMessage(),
                'trace'   => $e->getTrace(),
            ]);
            $this->abort($response->setStatus(500));
        }
    }
    public function abort(Response $response)
    {
        $this->terminate($response);
    }
}
