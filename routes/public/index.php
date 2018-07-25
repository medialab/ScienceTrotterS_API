<?php

$aConfig = [
  'prefix' => 'colors'
];
$router->group($aConfig, function () use ($router) {
    require __DIR__.'/colors.php';
});

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

$aConfig = [
  'prefix' => 'interestWay'
];
$router->group($aConfig, function () use ($router) {
    require __DIR__.'/interestWay.php';
});


$aConfig = [
  'prefix' => 'credits'
];
$router->group($aConfig, function () use ($router) {
    require __DIR__.'/credits.php';
});
