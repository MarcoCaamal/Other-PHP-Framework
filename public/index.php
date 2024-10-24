<?php

use SMFramework\App;
use SMFramework\Http\Contracts\MiddlewareContract;
use SMFramework\Http\Request;
use SMFramework\Http\Response;
use SMFramework\Routing\Route;
use SMFramework\Validation\Rule;
use SMFramework\Validation\Rules\Required;

require __DIR__ . "/../vendor/autoload.php";

class AuthMiddleware implements MiddlewareContract
{
    /**
     *
     * @param Request $request
     * @param Closure $next
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->headers('Authorization') === null) {
            return json(['message' => 'Not Authenticated']);
        }

        return $next();
    }
}

$app = App::bootstrap();

Route::post('/validate', fn (Request $request) => json($request->validate([
    'test' => 'required',
    'num' => 'number',
    'email' => ['required', 'email']
], [
    'email' => [
        Required::class => 'DAME EL CAMPO'
    ]
])));

Route::get('/middlewares', fn () => json(['GET OK']))->setMiddlewares([AuthMiddleware::class]);

Route::get('/html', fn (Request $request) => view('home', [
    'user' => 'Guest'
]));

Route::get('/form', fn () => view('form'));
Route::post('/form', function (Request $request) {
    return json($request->validate(['email' => 'email', 'name' => 'required']));
});

Route::get('/', function () {
    return Response::text('HELLO WORLD with Docker');
});

Route::get('/session', function (Request $request) {
    session()->remove('test');
    // session()->flash('test', 'test');
    return json(["id" => session()->id(), 'test' => session()->get('test', 'por defecto')]);
    // return json($_SESSION);
});

$app->run();
