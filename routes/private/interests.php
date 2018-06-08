<?php

$router->get('/list', 'InterestsAdminController@list');
$router->get('/{id:[a-z0-9-]+}', 'InterestsAdminController@get');

$router->post('/add', 'InterestsAdminController@insert');
$router->post('/update', 'InterestsAdminController@update');
$router->post('/delete', 'InterestsAdminController@delete');