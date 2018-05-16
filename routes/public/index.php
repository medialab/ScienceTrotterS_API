<?php

$aConfig = [
  'prefix' => 'cities'
];

$router->group($aConfig, function () use ($router) {
    require __DIR__.'/cities.php';
});
