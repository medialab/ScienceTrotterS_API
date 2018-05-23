<?php

$router->get('/get', 'CitiesAdminController@get');
$router->get('/list', 'CitiesAdminController@list');

$router->post('/add', 'CitiesAdminController@add');
$router->post('/update', 'CitiesAdminController@update');