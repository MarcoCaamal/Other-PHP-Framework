<?php

namespace Junk;

use Junk\Container\Container;
use Junk\Http\HttpNotFoundException;
use Junk\Http\Request;
use Junk\Http\Response;
use Junk\Routing\Router;
use Junk\Server\Contracts\ServerContract;
use Junk\Server\PHPNativeServer;
use Junk\View\Contracts\ViewContract;
use Junk\View\ViewEngine;

class App
{
    public Router $router;
    public Request $request;
    public ServerContract $server;
    public ViewContract $view;

    public static function bootstrap(): App
    {
        $app = singleton(self::class);
        $app->router = new Router();
        $app->server = new PHPNativeServer();
        $app->request = $app->server->getRequest();
        $app->view = new ViewEngine(__DIR__ . "/../views");

        return $app;
    }

    public function run()
    {
        try {
            $response = $this->router->resolve($this->request);
            $this->server->sendResponse($response);
        } catch (HttpNotFoundException $e) {
            $response = Response::text("Not Found")->setStatus(404);
            $this->server->sendResponse($response);
        } catch (\Exception $e) {
            print($e);
        }
    }
}
