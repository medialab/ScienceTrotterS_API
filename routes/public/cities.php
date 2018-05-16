<?php

use App\Models\Cities;
$router->get('/list', 'CitiesController@list');

$router->get('/{id:[a-z0-9-]+}', function($id) use ($router) {
	$this->validate($request, [
		'id' => 'required',
	]);

	$oCity = Cities::where('id', $oRequest->input('id'))->first();
	echo json_encode($oCity->toArray());
	exit;
});