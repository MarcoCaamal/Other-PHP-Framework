<?php

namespace OtherPHPFramework;

use Exception;
use OtherPHPFramework\Http\HttpNotFoundException;
use OtherPHPFramework\Http\Request;
use OtherPHPFramework\Http\Response;
use OtherPHPFramework\Routing\Router;
use OtherPHPFramework\Server\Contracts\ServerContract;
use OtherPHPFramework\Server\PHPNativeServer;
use OtherPHPFramework\Validation\Exceptions\ValidationException;
use OtherPHPFramework\View\Contracts\ViewContract;
use OtherPHPFramework\View\ViewEngine;

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
            $this->abort(Response::text("Not Found")->setStatus(404));
        } catch (ValidationException $e) {
            $this->abort(json($e->errors())->setStatus(422));
        } catch (Exception $e) {
            $response = json([
                'message' => $e->getMessage(),
                'trace'   => $e->getTrace(),
            ]);
            $this->abort($response);
        }
    }

    public function abort(Response $response)
    {
        $this->server->sendResponse($response);
    }
}
