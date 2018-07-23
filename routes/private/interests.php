<?php

$router->get('/list', 'InterestsAdminController@list');
$router->get('/{id:[a-z0-9-]+}', 'InterestsAdminController@get');
$router->get('listenCnt/{id:[a-z0-9-]+}', 'InterestsAdminController@listenCount');

$router->get('/byParcoursId/{id:[a-z0-9-]+}', 'InterestsAdminController@byParcoursId');
$router->get('/byNoParcours/{sCityId:[a-z0-9-]+}', 'InterestsAdminController@byNoParcours');

$router->post('/search', 'InterestsAdminController@search');
$router->post('/add', 'InterestsAdminController@insert');
$router->post('/update', 'InterestsAdminController@update');
$router->post('/delete', 'InterestsAdminController@delete');