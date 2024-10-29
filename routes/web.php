<?php

use SMFramework\Http\Request;
use SMFramework\Http\Response;
use SMFramework\Routing\Route;

Route::get('/', fn (Request $request) => Response::text("SMFramework"));
Route::get('/form', fn (Request $request) => view('form'));
