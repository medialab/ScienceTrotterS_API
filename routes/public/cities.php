<?php

$router->get('/list', 'CitiesController@list');
$router->get('/byId/{sCityId}', 'CitiesController@byId');
$router->get('/{id:[a-z0-9-]+}', 'CitiesAdminController@get');