<?php

$router->get('/', function () use ($router) {
    return 'private : ' . $router->app->version();
});

$router->get('/home', 'HomeController@home');

$aConfig = [
    'prefix' => 'users',
];

$router->group($aConfig, function () use ($router) {
    $router->post('/', 'UsersController@home');
    $router->get('/list', 'UsersController@list');

});
