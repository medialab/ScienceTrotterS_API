<?php

$router->get('/list', 'ParcoursController@list');
$router->get('/{id:[a-z0-9-]+}', 'CitiesController@get');