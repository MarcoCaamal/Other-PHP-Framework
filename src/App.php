<?php

namespace OtherPHPFramework;

use Exception;
use OtherPHPFramework\Http\HttpNotFoundException;
use OtherPHPFramework\Http\Request;
use OtherPHPFramework\Http\Response;
use OtherPHPFramework\Routing\Router;
use OtherPHPFramework\Server\Contracts\ServerContract;
use OtherPHPFramework\Server\PHPNativeServer;
use OtherPHPFramework\Session\PhpNativeSessionStorage;
use OtherPHPFramework\Session\Session;
use OtherPHPFramework\Validation\Exceptions\ValidationException;
use OtherPHPFramework\Validation\Rule;
use OtherPHPFramework\View\Contracts\ViewContract;
use OtherPHPFramework\View\ViewEngine;

class App
{
    public Router $router;
    public Request $request;
    public ServerContract $server;
    public ViewContract $view;
    public Session $session;

    public static function bootstrap(): App
    {
        $app = singleton(self::class);
        $app->router = new Router();
        $app->server = new PHPNativeServer();
        $app->request = $app->server->getRequest();
        $app->view = new ViewEngine(__DIR__ . "/../views");
        $app->session = new Session(new PhpNativeSessionStorage);
        Rule::loadDefaultRules();
        return $app;
    }

    public function run()
    {
        try {
            $response = $this->router->resolve($this->request);
            $this->server->sendResponse($response);
        } catch (HttpNotFoundException $e) {
            $this->abort(Response::text("Not Found")->setStatus(404));
        } catch (ValidationException $e) {
            $this->abort(json($e->errors())->setStatus(422));
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
        $this->server->sendResponse($response);
    }
}
