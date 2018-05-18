<?php

use App\Http\Controllers\ParcoursController;



$router->get('/list', function() {
	var_dump(class_exists("ParcoursController"));
	exit;
});
$router->get('/{id:[a-z0-9-]+}', 'ParcoursController@get');