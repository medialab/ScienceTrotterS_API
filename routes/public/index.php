<?php

/*$aConfig = [
  'prefix' => 'auth'
];*/
$router->group($aConfig, function () use ($router) {
    //require __DIR__.'/auth.php';
});

$aConfig = [
  'prefix' => 'cities'
];
$router->group($aConfig, function () use ($router) {

    require __DIR__.'/cities.php';

});

$aConfig = [
    'prefix' => 'users',
];
