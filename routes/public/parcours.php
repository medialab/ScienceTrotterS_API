<?php

$router->get('/list', 'ParcoursController@list');
$router->get('/length/{id:[a-z0-9-]+}', 'ParcoursController@length');
$router->get('/trace/{id:[a-z0-9-]+}', 'ParcoursController@trace');
$router->get('/closest/{id:[a-z0-9-]+}', 'ParcoursController@closest');

$router->get('/listen/{id:[a-z0-9-]+}', 'ParcoursController@listen');
$router->get('listenCnt/{id:[a-z0-9-]+}', 'ParcoursAdminController@listenCount');
//$router->get('/trace-test/{id:[a-z0-9-]+}', 'ParcoursController@traceTest');

$router->get('/{id:[a-z0-9-]+}', 'ParcoursController@get');
$router->get('/byCityId/{sCityId:[a-z0-9-]+}', 'ParcoursController@byCityId');
$router->get('/byId/{sParcourId:[a-z0-9-]+}', 'ParcoursController@byId');
