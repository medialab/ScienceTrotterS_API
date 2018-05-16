<?php

$router->get('/list', 'CitiesController@list');
$router->get('/{id:[a-z0-9-]+}', 'CitiesController@find');