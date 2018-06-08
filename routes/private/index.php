<?php

/* API INFOS */
$router->get('/', function () use ($router) {
    return 'private : ' . $router->app->version();
});

/* USERT */
	$aConfig = [
	    'prefix' => 'users',
	];
	$router->group($aConfig, function () use ($router) {
	    $router->get('/list', 'UsersController@list');
	});

/* PARCOURS */
	$aConfig = [
	    'prefix' => 'parcours',
	];
	$router->group($aConfig, function () use ($router) {
	    require __DIR__.'/parcours.php';
	});

/* CITY */
	$aConfig = [
	    'prefix' => 'cities',
	];
	$router->group($aConfig, function () use ($router) {
	    require __DIR__.'/cities.php';
	});

/* Interests */
	$aConfig = [
	    'prefix' => 'interests',
	];
	$router->group($aConfig, function () use ($router) {
	    require __DIR__.'/interests.php';
	});
