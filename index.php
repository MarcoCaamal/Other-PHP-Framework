<?php
require './Router.php';

$router = new Router();

$router->get('/test', function () { 
    return 'GET OK';
});

$router->post('/test', function () { 
    return 'POST OK';
});

try {
    $action = $router->resolve();
    print($action());
} catch (HttpNotFoundException $ex) {
    print($ex);
    http_response_code(404);
}