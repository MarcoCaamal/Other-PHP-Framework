<?php

use Junk\HttpNotFoundException;
use Junk\Router;

require __DIR__ . "/../vendor/autoload.php";

$router = new Router();

$router->get("/", function () { 
    return "Server is listen...";
});

$router->get('/test/{id}', function () { 
    return 'GET OK ';
});

$router->post('/test', function () { 
    return 'POST OK';
});

try {
    $route = $router->resolve($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    $action = $route->action();

    print($action());
} catch (HttpNotFoundException $ex) {
    print($ex);
    http_response_code(404);
}