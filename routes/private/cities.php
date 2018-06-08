<?php

$router->get('/list', 'CitiesAdminController@list');
$router->get('/{id:[a-z0-9-]+}', 'CitiesAdminController@get');

$router->post('/add', 'CitiesAdminController@insert');
$router->post('/update', 'CitiesAdminController@update');
$router->post('/delete', 'CitiesAdminController@delete');