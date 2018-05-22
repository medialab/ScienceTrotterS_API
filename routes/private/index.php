<?php

$router->get('/', function () use ($router) {
    return 'private : ' . $router->app->version();
});


$aConfig = [
    'prefix' => 'users',
];
$router->group($aConfig, function () use ($router) {
    $router->get('/list', 'UsersController@list');
});

$aConfig = [
    'prefix' => 'cities',
];
$router->group($aConfig, function () use ($router) {
    $router->post('/add', 'CitiesAdminController@add');
    $router->post('/update', 'CitiesAdminController@update');
});
