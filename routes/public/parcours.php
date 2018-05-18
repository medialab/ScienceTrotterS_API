<?php

use App\Http\Controllers\ParcoursAdminController;



$router->get('/list', function() {
	var_dump(class_exists("ParcoursAdminController"));
	exit;
});
$router->get('/{id:[a-z0-9-]+}', 'ParcoursController@get');