<?php

namespace SMFramework;

use Dotenv\Dotenv;
use Exception;
use SMFramework\Config\Config;
use SMFramework\Database\Contracts\DatabaseDriverContract;
use SMFramework\Database\ORM\Model;
use SMFramework\Http\HttpMethod;
use SMFramework\Http\HttpNotFoundException;
use SMFramework\Http\Request;
use SMFramework\Http\Response;
use SMFramework\Routing\Router;
use SMFramework\Server\Contracts\ServerContract;
use SMFramework\Session\Contracts\SessionStorageContract;
use SMFramework\Session\Session;
use SMFramework\Validation\Exceptions\ValidationException;
use SMFramework\View\Contracts\ViewContract;

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
        $this->server->sendResponse($response);
        $this->database->close();
    }
    public static function bootstrap(string $root): App
    {
        self::$root = $root;
        $app = singleton(self::class, self::class);
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
            $provider->registerServices();
        }
        return $this;
    }
    public function setHttpHandlers(): self
    {
        $this->router = singleton(Router::class);
        $this->server = app(ServerContract::class);
        $this->request = $this->server->getRequest();
        $this->session = singleton(Session::class, fn () => new Session(app(SessionStorageContract::class)));
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
            $this->abort(back()->withErrors($e->errors(), 422));
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
