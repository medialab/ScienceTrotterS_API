<?php
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
  'prefix' => 'private'
];

$router->group($aPrivateConfig, function () use ($router) {

    require __DIR__.'/private/index.php';

});



$router->get('/initialize_database', 'HomeController@initialize_database');
$router->get('/initialize_database_mockup', 'HomeController@initialize_database_mockup');