<?php

$router->get('/list', 'ParcoursController@list');
$router->get('/{id:[a-z0-9-]+}', 'ParcoursController@get');

$router->post('/add', 'ParcoursController@insert');
$router->post('/update', 'ParcoursController@update');
$router->post('/delete', 'ParcoursController@delete');