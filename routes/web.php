<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/hello-lumen', function () {
    return "<h1>Lumen</h1><p>Hi good developer, thank for using Lumen</p>";
});

$router->get('/hello-lumen/{name}', function ($name) {
    return "<h1>Lumen</h1><p>Hi <b>" . $name . "</b>, thank for using Lumen</p>";
});

$router->get('/scores', ['middleware' => 'login', function () {
    return "<h1>Selamat</h1><p>Nilai anda 100</p>";
}]);

$router->get('/users', 'UsersController@index');
$router->get('/users/{userId}', 'UsersController@show');
$router->get('posts', 'PostsController@index');

// Product
$router->get('/products', 'ProductsController@index');
$router->post('/products', 'ProductsController@store');
$router->get('/products/{id}', 'ProductsController@show');
$router->put('/products/{id}', 'ProductsController@update');
$router->delete('/products/{id}', 'ProductsController@destroy');