<?php

namespace Junk;

use Junk\Container\Container;
use Junk\Http\HttpNotFoundException;
use Junk\Http\Request;
use Junk\Http\Response;
use Junk\Routing\Router;
use Junk\Server\Contracts\ServerContract;
use Junk\Server\PHPNativeServer;

class App
{
    public Router $router;
    public Request $request;
    public ServerContract $server;

    public static function bootstrap(): App
    {
        $app = Container::singleton(self::class);
        $app->router = new Router();
        $app->server = new PHPNativeServer();
        $app->request = $app->server->getRequest();

        return $app;
    }

    public function run()
    {
        try {
            $route = $this->router->resolve($this->request);
            $this->request->setRoute($route);
            $action = $route->action();
            $response = $action($this->request);
            $this->server->sendResponse($response);
        } catch (HttpNotFoundException $e) {
            $response = Response::text("Not Found")->setStatus(404);
            $this->server->sendResponse($response);
        } catch (\Exception $e) {
            print($e);
        }
    }
}
