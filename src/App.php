<?php

namespace SMFramework;

use Dotenv\Dotenv;
use Exception;
use SMFramework\Config\Config;
use SMFramework\Database\Contracts\DatabaseDriverContract;
use SMFramework\Database\ORM\Model;
use SMFramework\Database\PdoDriver;
use SMFramework\Http\HttpMethod;
use SMFramework\Http\HttpNotFoundException;
use SMFramework\Http\Request;
use SMFramework\Http\Response;
use SMFramework\Routing\Router;
use SMFramework\Server\Contracts\ServerContract;
use SMFramework\Server\PHPNativeServer;
use SMFramework\Session\PhpNativeSessionStorage;
use SMFramework\Session\Session;
use SMFramework\Validation\Exceptions\ValidationException;
use SMFramework\Validation\Rule;
use SMFramework\View\Contracts\ViewContract;
use SMFramework\View\ViewEngine;

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
        Dotenv::createImmutable($root);
        Config::load("$root/config");
        $app = singleton(self::class, self::class);
        $app->router = new Router();
        $app->server = new PHPNativeServer();
        $app->request = $app->server->getRequest();
        $app->view = new ViewEngine(config('view.path'));
        $app->session = new Session(new PhpNativeSessionStorage());
        $app->database = singleton(DatabaseDriverContract::class, PdoDriver::class);
        $app->database->connect('mysql', 'localhost', 3306, 'exam', 'root', '');
        Rule::loadDefaultRules();
        Model::setDatabaseDriver($app->database);
        return $app;
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
