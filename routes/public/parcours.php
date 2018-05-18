<?php

use App\Http\Controllers\ParcoursController AS Parcours;
use App\Http\Controllers\CitiesController AS CityCtrl;
use App\Http\Controllers\UsersController AS UserCtrl;

$router->get('/list', function() {
	var_dump("Parcours", class_exists("Parcours"));
	var_dump("Cities", class_exists("CityCtrl"));
	var_dump("Cities", class_exists("CityCtrl"));
	var_dump("Users", class_exists("UserCtrl"));
	exit;
});

$router->get('/{id:[a-z0-9-]+}', 'ParcoursController@get');