<?php

$router->get('/list', 'ParcoursController@list');
$router->get('/{id:[a-z0-9-]+}', 'ParcoursController@get');

$router->post('/add', 'ParcoursAdminController@insert');
$router->post('/update', 'ParcoursAdminController@update');
$router->post('/delete', 'ParcoursAdminController@delete');