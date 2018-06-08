<?php

$aConfig = [
  'prefix' => 'cities'
];
$router->group($aConfig, function () use ($router) {
    require __DIR__.'/cities.php';
});

$aConfig = [
  'prefix' => 'parcours'
];
$router->group($aConfig, function () use ($router) {
    require __DIR__.'/parcours.php';
});


$aConfig = [
  'prefix' => 'interests'
];
$router->group($aConfig, function () use ($router) {
    require __DIR__.'/interests.php';
});
