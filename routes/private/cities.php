<?php

$router->get('/get', 'CitiesAdminController@get');
$router->get('/list', 'CitiesAdminController@list');

$router->post('/add', 'CitiesAdminController@insert');
$router->post('/update', 'CitiesAdminController@update');