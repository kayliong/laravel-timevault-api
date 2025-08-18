<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/ping', function () {
    return "Hello World!";
});

$router->group(['prefix'=>'api/v1/', 'middleware' => ['verifyApiRequest']], function () use($router) {
    $router->post('/object', 'Objects\ObjectController@createObject');
    $router->get('/object/get_all_records', 'Objects\ObjectController@getAllRecords');
    $router->get('/object/{key}', 'Objects\ObjectController@getObjectByKey');
});
