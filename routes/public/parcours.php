<?php

use App\Http\Controllers\ParcoursController;
use App\Http\Controllers\CitiesController;

$router->get('/list', function() {
	var_dump("Parcours", class_exists("ParcoursController"));
	var_dump("Cities", class_exists("CitiesController"));
	exit;
});

$router->get('/{id:[a-z0-9-]+}', 'ParcoursController@get');