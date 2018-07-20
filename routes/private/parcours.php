<?php

$router->get('/list', 'ParcoursAdminController@list');
$router->get('/length/{id:[a-z0-9-]+}', 'ParcoursController@length');
$router->get('/{id:[a-z0-9-]+}', 'ParcoursAdminController@get');
$router->get('listenCnt/{id:[a-z0-9-]+}', 'ParcoursAdminController@listenCount');

$router->get('/byCityId/{id:[a-z0-9-]+}', 'ParcoursAdminController@byCityId');
$router->get('/byNoCity', 'ParcoursAdminController@byNoCity');

$router->post('/search', 'ParcoursAdminController@search');
$router->post('/add', 'ParcoursAdminController@insert');
$router->post('/update', 'ParcoursAdminController@update');
$router->post('/delete', 'ParcoursAdminController@delete');