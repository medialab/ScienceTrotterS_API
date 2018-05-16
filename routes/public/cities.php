<?php

use App\Models\Cities;

$router->get('/list', 'CitiesController@list');

$router->get('/id', 'CitiesController@find');


$router->get('/{id:[a-z0-9-]+}', function($id) use ($router) {
	$oCity = Cities::where('id', $id)->first();
	echo json_encode($oCity->toArray());
	exit;
});