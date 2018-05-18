<?php

$router->get('/list', 'TestController@list');
$router->get('/{id:[a-z0-9-]+}', 'CitiesController@get');