<?php

$router->post('/login', 'UsersController@login');
$router->get('/logout', 'UsersController@logout');

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
|
*/
$aPublicConfig = [
  'prefix' => 'public'
];

$router->group($aPublicConfig, function () use ($router) {

    require __DIR__.'/public/index.php';

});

/*
|--------------------------------------------------------------------------
| Private routes
|--------------------------------------------------------------------------
|
*/
$aPrivateConfig = [
  'prefix' => 'private',
  'middleware' => 'auth'
];

$router->group($aPrivateConfig, function () use ($router) {

    require __DIR__.'/private/index.php';

});
